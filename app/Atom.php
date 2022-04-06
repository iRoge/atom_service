<?php

namespace App;

use App\Exceptions\AtomNotFoundException;
use App\Exceptions\DataMatrixCodeAllreadyExists;
use App\Exceptions\NewAtomStateAreSameAsLast;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Throwable;

/**
 * @method static Builder|Atom create($value)
 * @method static Builder|Atom make($value)
 * @package App
 */
class Atom extends Model
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data_matrix_code',
        'assortment_id',
        'admin_id',
        'place_id',
        'place_type',
        'nds_bid',
        'commission',
        'purchase_invoice_item_id',
        'selling_price',
        'event_id',
        'updated_at',
        'created_at'
    ];

    /**
     * Метод возвращает последнюю информацию о товаре ( кто изменил, где лежит итд )
     *
     * @throws AtomNotFoundException
     */
    public static function getLastAtomState($atom_id)
    {
        $atomsTable = (new Atom)->getTable();
        $atomQuery = DB::table($atomsTable)
            ->select('id',
                'data_matrix_code',
                'assortment_id',
                'nds_bid',
                'commission',
                'admin_id',
                'updated_at as created_at',
                'place_type',
                'place_id',
                'purchase_invoice_item_id',
                'selling_price',
                'event_id'
            )->where('id', '=', $atom_id);

        $lastAtomInfo = $atomQuery->first();
        if (!$lastAtomInfo) {
            throw new AtomNotFoundException();
        }

        return $lastAtomInfo;
    }

    /**
     * Метод возвращает последнюю информацию об атомах по их id
     *
     * @return array
     */
    public static function getLastAtomStates($atomIds)
    {
        $atomsTable = (new Atom)->getTable();
        $atomQuery = DB::table($atomsTable)
            ->select('id',
                'data_matrix_code',
                'assortment_id',
                'nds_bid',
                'commission',
                'admin_id',
                'updated_at as created_at',
                'place_type',
                'place_id',
                'purchase_invoice_item_id',
                'selling_price',
                'event_id'
            )->whereIn('id', $atomIds);

        $atoms = $atomQuery->get()->toArray();
        if (empty($atoms)) {
            $atoms = [];
        }

        return $atoms;
    }

    /**
     * Создаем в системе атом. Он создается вместе с записью в истории перемещений.
     *
     * @param $requestAttributes
     * @return false|Atom createdAtom or false
     * @throws DataMatrixCodeAllreadyExists|Throwable
     */
    public static function addAtom(array $requestAttributes)
    {
        try {
            if ($requestAttributes['data_matrix_code']) {
                $matrixCodes = [$requestAttributes['data_matrix_code']];
                $existingAtoms = self::getByMatrixCodes($matrixCodes);
                if (!empty($existingAtoms)) {
                    throw new DataMatrixCodeAllreadyExists(
                        'атомы со следующими кодами, уже существуют: '
                        . implode(' ', array_column($existingAtoms, 'data_matrix_code'))
                    );
                }
            }
            DB::beginTransaction();
            $atom = Atom::create($requestAttributes);
            $atom->saveOrFail();
            $atomHistory = AtomHistory::createByAtom($atom);

            if ($atomHistory === false) {
                throw new Exception();
            }
            DB::commit();
            return $atom;
        } catch (QueryException $queryException) {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062) {
                DB::rollBack();
                throw new DataMatrixCodeAllreadyExists('Этот DataMatrix код уже был зарегистрирован у нас в системе.');
            }
            throw $queryException;
        } catch (Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

    /**
     * Создаем в системе атомы. Они создаются вместе с записью в истории перемещений.
     *
     * @param array $requestAttributes
     * @return array createdAtom or false
     * @throws DataMatrixCodeAllreadyExists
     * @throws Throwable
     */
    public static function addAtoms(array $requestAttributes)
    {
        try {
            $atomsToAdd = $requestAttributes['atomsToAdd'];
            $matrixCodes = array_column($atomsToAdd, 'data_matrix_code');
            if (!empty($matrixCodes)) {
                $existingAtoms = self::getByMatrixCodes($matrixCodes);
                if (!empty($existingAtoms)) {
                    throw new DataMatrixCodeAllreadyExists(
                        'атомы со следующими кодами, уже существуют: '
                        . implode(' ', array_column($existingAtoms, 'data_matrix_code'))
                    );
                }
            }
            $now = date("Y-m-d H:i:s");
            DB::beginTransaction();
            $arAtomsHistoryToInsert = [];
            $createdAtoms = [];
            foreach ($atomsToAdd as $atom) {
                /** @var Atom $newAtom */
                $newAtom = Atom::create($atom);
                $newAtom->saveOrFail();
                $arAtomsHistoryToInsert[] = [
                    'atom_id' => $newAtom->id,
                    'parent_id' => null,
                    'admin_id' => $atom['admin_id'],
                    'place_type' => $atom['place_type'],
                    'place_id' => $atom['place_id'],
                    'nds_bid' => $atom['nds_bid'],
                    'commission' => $atom['commission'],
                    'selling_price' => $atom['selling_price'],
                    'event_id' => $atom['event_id'],
                    'created_at' => $now,
                ];
                $createdAtoms[$newAtom->id] = [
                    'id' => $newAtom->id,
                    'assortment_id' => $newAtom->assortment_id,
                    'admin_id' => $atom['admin_id'],
                    'place_type' => $atom['place_type'],
                    'place_id' => $atom['place_id'],
                    'nds_bid' => $atom['nds_bid'],
                    'commission' => $atom['commission'],
                    'data_matrix_code' => $atom['data_matrix_code'],
                    'purchase_invoice_item_id' => $atom['purchase_invoice_item_id'],
                    'selling_price' => $atom['selling_price'],
                    'event_id' => $atom['event_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($arAtomsHistoryToInsert,1000) as $arInsert)
            {
                DB::table((new AtomHistory())->getTable())->insert($arInsert);
                foreach ($arInsert as $history) {
                    $createdAtoms[$history['atom_id']]['created_at'] = $now;
                }
            }
            DB::commit();

            return $createdAtoms;
        } catch (QueryException $queryException) {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062) {
                DB::rollBack();
                throw new DataMatrixCodeAllreadyExists('Этот DataMatrix код уже был зарегистрирован у нас в системе.');
            }
        } catch (Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }

        return null;
    }

    public static function getByMatrixCodes($matrixCodes): array
    {
        $atomsTable = (new Atom)->getTable();
        $atomQuery = DB::table($atomsTable)
            ->select('id',
                'data_matrix_code',
                'assortment_id',
                'nds_bid',
                'commission',
                'admin_id',
                'updated_at as created_at',
                'place_type',
                'place_id',
                'purchase_invoice_item_id',
                'selling_price',
                'event_id'
            )->whereIn('data_matrix_code', $matrixCodes);

        return $atomQuery->get()->toArray();
    }

    /**
     * Удаляет атом без следов
     * @param int $atomId
     * @throws Exception
     */
    public static function deleteOne(int $atomId)
    {
        Atom::find($atomId)->delete();
    }

    /**
     * Удаляет атомы по массиву идентификаторов
     * @param array $atomIds
     * @throws Exception
     */
    public static function deleteMany(array $atomIds)
    {
        DB::table((new Atom)->getTable())
            ->whereIn("id", $atomIds)
            ->delete();
    }

    /**
     * Изменяет первое состояние нескольким товарам(атомам) в истории
     *
     * @param array $arAtoms
     * @throws AtomNotFoundException
     * @throws NewAtomStateAreSameAsLast
     */
    public static function changeActualAtomsPurchaseItems(array $arAtoms): void
    {
        foreach ($arAtoms as $atomState) {
            $obAtom = self::whereId($atomState['id'])->first();
            if (!$obAtom) {
                throw new AtomNotFoundException('Atom with ' . $atomState['id'] . ' id not found.');
            }
            $dataToSave = [
                'purchase_invoice_item_id' => $atomState['purchase_invoice_item_id'],
            ];
            DB::table((new Atom())->getTable())->where('id', '=', $obAtom->id)->update($dataToSave);
        }
    }
}
