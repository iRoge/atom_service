<?php

namespace App;

use App\Exceptions\AtomNotFoundException;
use App\Exceptions\NewAtomStateAreSameAsLast;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Throwable;


/**
 * @method static Builder|AtomHistory make($value)
 * @package App
 */
class AtomHistory extends Model
{

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    public $timestamps = true;

    const DEFAULT_ADMIN_ID = 0;

    protected $table = 'atom_history';

    protected $fillable = [
        'parent_id',
        'atom_id',
        'admin_id',
        'place_id',
        'place_type',
        'nds_bid',
        'commission',
        'selling_price',
        'event_id',
        'updated_at',
        'created_at'
    ];

    /**
     * Создает первую запись в истории атома
     *
     * @param Atom $atom
     * @return AtomHistory  успешно созданноая запись в истории перемещения атома либо false
     * @throws Throwable
     */
    public static function createByAtom(Atom $atom)
    {
        $atomHistory = new AtomHistory;
        $atomHistory->admin_id = $atom->admin_id;
        $atomHistory->atom_id = $atom->id;
        $atomHistory->parent_id = null;
        $atomHistory->place_type = $atom->place_type;
        $atomHistory->place_id = $atom->place_id;
        $atomHistory->nds_bid = $atom->nds_bid;
        $atomHistory->commission = $atom->commission;
        $atomHistory->selling_price = $atom->selling_price;
        $atomHistory->event_id = $atom->event_id;
        $atomHistory->created_at = $atom->updated_at;
        $atomHistory->updated_at = $atom->updated_at;

        try {
            $atomHistory->saveOrFail();
        } catch (Exception $e) {
            print_r($e->getMessage());
            throw $e;
        }

        return $atomHistory;
    }


    /**
     * Сохраняет новое состояние товара(атома) в истории
     *
     * @param int $atom_id
     * @param array $newAtomState
     * @throws AtomNotFoundException
     * @throws NewAtomStateAreSameAsLast
     * @throws Throwable
     */
    public static function addAtomState(int $atom_id, array $newAtomState): void
    {
        $atom = Atom::where('id', '=', $atom_id)->first();
        if (!$atom) {
            throw new AtomNotFoundException('Atom with ' . $atom_id . ' id not found.');
        }

        if (
            $newAtomState['place_id'] == $atom->id_place
            && $newAtomState['place_type'] == $atom->place_type
            && $newAtomState['event_id'] == $atom->event_id
        ) {
            throw new NewAtomStateAreSameAsLast(
                'You are trying to change state of atom to same position. Atom with id '
                . $atom['id']
                . ' has place_id = ' . $atom->place_id
                . ' , place_type = ' . $atom->place_type
                . ' and event_id = ' . $atom->event_id
            );
        }

        $timeUpdated = $newAtomState['changed_at'] ?: Carbon::now()->toDateTime();
        $newAtomHistory = AtomHistory::make($newAtomState);
        $newAtomHistory->admin_id = $newAtomState['admin_id'];
        $newAtomHistory->place_id = $newAtomState['place_id'];
        $newAtomHistory->place_type = $newAtomState['place_type'];
        $newAtomHistory->atom_id = $atom_id;
        $newAtomHistory->nds_bid = $newAtomState['nds_bid'];
        $newAtomHistory->commission = $newAtomState['commission'];
        $newAtomHistory->selling_price = $newAtomState['selling_price'];
        $newAtomHistory->event_id = $newAtomState['event_id'];
        $newAtomHistory->created_at = $timeUpdated;
        $newAtomHistory->updated_at = $timeUpdated;
        $newAtomHistory->saveOrFail();

        $atom->admin_id = $newAtomState['admin_id'];
        $atom->place_id = $newAtomState['place_id'];
        $atom->place_type = $newAtomState['place_type'];
        $atom->nds_bid = $newAtomState['nds_bid'];
        $atom->commission = $newAtomState['commission'];
        $atom->updated_at = $timeUpdated;
        $atom->purchase_invoice_item_id = $newAtomState['purchase_invoice_item_id'];
        $atom->selling_price = $newAtomState['selling_price'];
        $atom->event_id = $newAtomState['event_id'];
        $atom->saveOrFail();
    }

    /**
     * Сохраняет новое состояние нескольким товарам(атомам) в истории
     *
     * @param array $arAtoms
     * @throws AtomNotFoundException
     * @throws NewAtomStateAreSameAsLast
     */
    public static function addAtomStates(array $arAtoms): void
    {
        $dataToSave = [];
        foreach ($arAtoms as $atomState) {
            $atom = Atom::where('id', '=', $atomState['id'])->first();
            if (!$atom) {
                throw new AtomNotFoundException('Atom with ' . $atomState['id'] . ' id not found.');
            }

            $isNewStateSameAsLast = (
                ($atomState['place_id'] == $atom->place_id)
                && ($atomState['place_type'] == $atom->place_type)
                && ($atomState['event_id'] == $atom->event_id)
            );

            if ($isNewStateSameAsLast) {
                throw new NewAtomStateAreSameAsLast(
                    'You are trying to change state of atom to same position. Atom with id '
                    . $atomState['id']
                    . ' has place_id = ' . $atom->place_id
                    . ' , place_type = ' . $atom->place_type
                    . ' and event_id = ' . $atom->event_id
                );
            }
            $timeUpdated = $atomState['changed_at'] ?: Carbon::now()->toDateTime();
            $dataToSave[] = [
                'admin_id' => $atomState['admin_id'],
                'place_id' => $atomState['place_id'],
                'place_type' => $atomState['place_type'],
                'atom_id' => $atom->id,
                'nds_bid' => $atomState['nds_bid'],
                'commission' => $atomState['commission'],
                'selling_price' => $atomState['selling_price'],
                'event_id' => $atomState['event_id'],
                'created_at' => $timeUpdated,
                'updated_at' => $timeUpdated,
            ];
            $atom->admin_id = $atomState['admin_id'];
            $atom->place_id = $atomState['place_id'];
            $atom->place_type = $atomState['place_type'];
            $atom->nds_bid = $atomState['nds_bid'];
            $atom->commission = $atomState['commission'];
            $atom->updated_at = $timeUpdated;
            $atom->purchase_invoice_item_id = $atomState['purchase_invoice_item_id'];
            $atom->selling_price = $atomState['selling_price'];
            $atom->event_id = $atomState['event_id'];
            $atom->saveOrFail();
        }
        foreach (array_chunk($dataToSave,1000) as $arInsert)
        {
            DB::table((new AtomHistory)->getTable())->insert($arInsert);
        }
    }

    /**
     * Метод возвращает предпоследнюю информацию о товаре
     *
     * @param array $atoms
     * @param int $admin_id
     * @return array
     * @throws AtomNotFoundException
     * @throws NewAtomStateAreSameAsLast
     * @throws Throwable
     */
    public static function returnAtomsToPreviousPlace(array $atoms): array
    {
        /**
         * @var $lastAtomInfo AtomHistory|Builder
         */
        $atomHistoryTable = (new AtomHistory)->getTable();
        $atomsTable = (new Atom)->getTable();
        $savedAtoms = [];
        $atomsToDelete = [];
        $historyToDelete = [];
        // Заполняем массив с количеством историй к удалению по каждому атому
        $atomsHistoryCountToDelete = [];
        foreach ($atoms as $atom) {
            if (!isset($atomsHistoryCountToDelete[$atom['id']])) {
                $atomsHistoryCountToDelete[$atom['id']] = 1;
            } else {
                $atomsHistoryCountToDelete[$atom['id']]++;
            }
        }

        foreach ($atomsHistoryCountToDelete as $atomId => $count) {
            $atomInfoQuery = AtomHistory::whereAtomId($atomId)
                ->select($atomsTable . '.data_matrix_code',
                    $atomsTable . '.assortment_id',
                    $atomHistoryTable . '.nds_bid',
                    $atomHistoryTable . '.commission',
                    $atomHistoryTable . '.admin_id',
                    $atomHistoryTable . '.atom_id as id',
                    $atomHistoryTable . '.created_at',
                    $atomHistoryTable . '.parent_id',
                    $atomHistoryTable . '.place_type',
                    $atomHistoryTable . '.place_id',
                    $atomsTable . '.purchase_invoice_item_id',
                    $atomHistoryTable . '.selling_price',
                    $atomHistoryTable . '.event_id',
                    $atomHistoryTable . '.id as history_id',
                    $atomsTable . '.id as atom_id',
                )
                ->orderByDesc('atom_history.id')
                ->leftJoin('atoms', $atomHistoryTable . '.atom_id', '=', $atomsTable . '.id');
            // Собираем всю историю по атому
            $atomHistory = $atomInfoQuery->get()->toArray();

            // Вычисляем последнюю и заносим в список для удаления,
            // если последней истории нет - удаляем атом
            if (!isset($atomHistory[0])) {
                $atomsToDelete[$atomId] = $atomId;
                continue;
            }
            $lastAtomInfo = $atomHistory[0];

            $historyToDelete[$lastAtomInfo['history_id']] = $lastAtomInfo['history_id'];

            // Если количество историй к удалению по атому больше 1,
            // то вычисляем самую старую историю к удалению, и, если ее нет, то сразу заносим атом к удалению,
            // а если есть, то через foreach заносим все истории от предпоследней (т.к. последнюю уже занесли) до той,
            // которую нужно удалить самую старую.
            // Если же удалить нужно всего 1 историю, то вычисляем последнюю и предпоследнюю истории,
            // если их нет, то удаляем атом, если есть - удаляем только последнюю историю
            if ($count > 1) {
                $privousHistoryFromTheOldiestHistoryToDelete = $atomHistory[$count] ?? null;
                if (!$privousHistoryFromTheOldiestHistoryToDelete) {
                    $atomsToDelete[$atomId] = $atomId;
                    continue;
                }
                $countToDeleteLeft = $count - 1;
                while ($countToDeleteLeft > 0) {
                    $history = $atomHistory[$countToDeleteLeft];
                    $historyToDelete[$privousHistoryFromTheOldiestHistoryToDelete['history_id']] = $history['history_id'];
                    $countToDeleteLeft--;
                }

                $actualHistory = $atomHistory[$count];
            } else {
                if (!isset($atomHistory[1])) {
                    $atomsToDelete[$atomId] = $atomId;
                    continue;
                }
                $actualHistory = $atomHistory[1];
            }

            // меняем актуальное местоположение атома
            /** @var \App\Atom $atomToChange */
            $atomToChange = Atom::where('id', '=', $atomId)->first();
            $atomToChange->admin_id = $actualHistory['admin_id'];
            $atomToChange->place_id = $actualHistory['place_id'];
            $atomToChange->place_type = $actualHistory['place_type'];
            $atomToChange->nds_bid = $actualHistory['nds_bid'];
            $atomToChange->commission = $actualHistory['commission'];
            $atomToChange->selling_price = $actualHistory['selling_price'];
            $atomToChange->event_id = $actualHistory['event_id'];
            $atomToChange->updated_at = $actualHistory['created_at'];
            $atomToChange->saveOrFail();
            $savedAtoms[] = [
                'id' => $atomToChange->id,
                'data_matrix_code' => $atomToChange->data_matrix_code,
                'assortment_id' => $atomToChange->assortment_id,
                'admin_id' => $atomToChange->admin_id,
                'place_id' => $atomToChange->place_id,
                'place_type' => $atomToChange->place_type,
                'nds_bid' => $atomToChange->nds_bid,
                'commission' => $atomToChange->commission,
                'purchase_invoice_item_id' => $atomToChange->purchase_invoice_item_id,
                'selling_price' => $atomToChange->selling_price,
                'event_id' => $atomToChange->event_id,
                'created_at' => $atomToChange->updated_at,
            ];
        }
        if (!empty($atomsToDelete)) {
            DB::table($atomsTable)->whereIn('id', $atomsToDelete)->delete();
        }

        if (!empty($historyToDelete)) {
            DB::table($atomHistoryTable)->whereIn('id', $historyToDelete)->delete();
        }

        return $savedAtoms;
    }

    /**
     * Возвращает массив с информацией об атомах расположенных по указанным адресам.
     *
     * @param array|null $places
     * @param int|null $assortmentId
     * @param null $commission
     * @param int $findingMode // Тип поиска: 1 - по актуальной позиции атома, 2 - по первой позиции атома, 3 - по всей истории атома
     * @param bool $requireMarkedAtoms
     * @param null $eventsIds
     * @param null $dataMatrixCodes
     * @param null $purchaseInvoiceItemsIds
     * @return array|null
     */
    public static function getAtomsStates(
        array $places = null,
        int $assortmentId = null,
        $commission = null,
        int $findingMode = 1,
        bool $requireMarkedAtoms = true,
        $eventsIds = null,
        $dataMatrixCodes = null,
        $purchaseInvoiceItemsIds = null
    ): ?array
    {
        // На всякий случай делаем унификацию местоположений
        $arrayUniqs = [];
        if (!empty($places)) {
            foreach ($places as $key => $place) {
                $uniqStr = 'placeType' . $place['placeType'] . 'placeId' . $place['placeId'];
                if (in_array($uniqStr, $arrayUniqs)) {
                    unset($places[$key]);
                    continue;
                }
                $arrayUniqs[] = $uniqStr;
            }
            // делаем чанк местоположений на массивы по 2500 штук, чтобы не вылетала ошибка большого запроса mysql
            $placesChunks = array_chunk($places, 2500);
        }
        $resultArray = [];
        $atomsTable = (new Atom)->getTable();
        $atomHistoryTable = (new AtomHistory)->getTable();
        if ($findingMode === 1) {
            $atomsQuery = DB::table($atomsTable)
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
                )
                ->orderByDesc($atomsTable . '.updated_at');;
            if (!$requireMarkedAtoms) {
                $atomsQuery->whereNull($atomsTable . '.data_matrix_code');
            }
            if ($commission !== null) {
                $atomsQuery->where($atomsTable . '.commission', $commission);
            }
            if ($assortmentId !== null) {
                $atomsQuery->where($atomsTable . '.assortment_id', $assortmentId);
            }
            if ($eventsIds !== null) {
                $atomsQuery->whereIn($atomsTable . '.event_id', $eventsIds);
            }
            if ($requireMarkedAtoms && $dataMatrixCodes !== null) {
                $atomsQuery->whereIn($atomsTable . '.data_matrix_code', $dataMatrixCodes);
            }
            if ($purchaseInvoiceItemsIds !== null) {
                $atomsQuery->whereIn($atomsTable . '.purchase_invoice_item_id', $purchaseInvoiceItemsIds);
            }

            if (!empty($places)) {
                foreach ($placesChunks as $placesChunk) {
                    $chunkQuery = clone $atomsQuery;
                    $chunkQuery->where(function ($query) use ($atomsTable, $placesChunk) {
                        foreach ($placesChunk as $place) {
                            $query->orWhere(function ($query) use ($atomsTable, $place) {
                                $query->where($atomsTable . '.place_type', '=', $place['placeType'])
                                    ->where($atomsTable . '.place_id', '=', $place['placeId']);
                            });
                        }
                    });
                    $resultArray = array_merge($resultArray, $chunkQuery->get()->toArray());
                }
            } else {
                $resultArray = $atomsQuery->get()->toArray();
            }
        } elseif ($findingMode === 3 || $findingMode === 2) {
            $atomsQuery = DB::table($atomHistoryTable)
                ->leftJoin($atomsTable, $atomHistoryTable . '.atom_id', '=', $atomsTable . '.id')
                ->select($atomsTable . '.data_matrix_code',
                    $atomsTable . '.assortment_id',
                    $atomHistoryTable . '.nds_bid',
                    $atomHistoryTable . '.commission',
                    $atomHistoryTable . '.admin_id',
                    $atomHistoryTable . '.atom_id as id',
                    $atomHistoryTable . '.created_at',
                    $atomHistoryTable . '.parent_id',
                    $atomHistoryTable . '.place_type',
                    $atomHistoryTable . '.place_id',
                    $atomsTable . '.purchase_invoice_item_id',
                    $atomHistoryTable . '.selling_price',
                    $atomHistoryTable . '.event_id',
                )
                ->orderByDesc($atomHistoryTable . '.updated_at');

            if ($findingMode === 2) {
                $firstHistoryByAtomsQuery = DB::table($atomHistoryTable)
                    ->distinct()
                    ->select(DB::raw('MIN(id) as id'))
                    ->groupBy('atom_id');

                if ($commission !== null) {
                    $firstHistoryByAtomsQuery->where('commission', $commission);
                }

                $firstHistoryByAtoms = $firstHistoryByAtomsQuery->get();
                $arFirstHistoryIdsByAtoms = [];
                /** @var AtomHistory $history */
                foreach ($firstHistoryByAtoms as $history) {
                    $arFirstHistoryIdsByAtoms[] = $history->id;
                }

                $atomsQuery->whereIn($atomHistoryTable . '.id', $arFirstHistoryIdsByAtoms);
            }

            if ($commission !== null) {
                $atomsQuery->where($atomHistoryTable . '.commission', $commission);
            }
            if ($assortmentId !== null) {
                $atomsQuery->where($atomsTable . '.assortment_id', $assortmentId);
            }
            if ($eventsIds !== null) {
                $atomsQuery->whereIn($atomHistoryTable . '.event_id', $eventsIds);
            }
            if ($requireMarkedAtoms && $dataMatrixCodes !== null) {
                $atomsQuery->whereIn($atomsTable . '.data_matrix_code', $dataMatrixCodes);
            }
            if ($purchaseInvoiceItemsIds !== null) {
                $atomsQuery->whereIn($atomsTable . '.purchase_invoice_item_id', $purchaseInvoiceItemsIds);
            }

            if (!empty($places)) {
                foreach ($placesChunks as $placesChunk) {
                    $chunkQuery = clone $atomsQuery;
                    $chunkQuery->where(function ($query) use ($atomHistoryTable, $placesChunk) {
                        foreach ($placesChunk as $place) {
                            $query->orWhere(function ($query) use ($atomHistoryTable, $place) {
                                $query->where($atomHistoryTable . '.place_type', '=', $place['placeType'])
                                    ->where($atomHistoryTable . '.place_id', '=', $place['placeId']);
                            });
                        }
                    });
                    $resultArray = array_merge($resultArray, $chunkQuery->get()->toArray());
                }
            } else {
                $resultArray = $atomsQuery->get()->toArray();
            }
        } else {
            return null;
        }

        return $resultArray;
    }

    public static function getFirstAtomStates($atomsIDs): array
    {
        $atomsTable = (new Atom)->getTable();
        $atomHistoryTable = (new AtomHistory)->getTable();
        $atomsQuery = DB::table($atomHistoryTable)
            ->leftJoin($atomsTable, $atomHistoryTable . '.atom_id', '=', $atomsTable . '.id')
            ->select($atomsTable . '.data_matrix_code',
                $atomsTable . '.assortment_id',
                $atomHistoryTable . '.nds_bid',
                $atomHistoryTable . '.commission',
                $atomHistoryTable . '.admin_id',
                $atomHistoryTable . '.atom_id as id',
                $atomHistoryTable . '.created_at',
                $atomHistoryTable . '.parent_id',
                $atomHistoryTable . '.place_type',
                $atomHistoryTable . '.place_id',
                $atomsTable . '.purchase_invoice_item_id',
                $atomHistoryTable . '.selling_price',
                $atomHistoryTable . '.event_id',
            )
            ->whereIn($atomHistoryTable . '.atom_id', $atomsIDs);

        $firstHistoryByAtoms = DB::table($atomHistoryTable)
            ->distinct()
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('atom_id')
            ->get();
        $arFirstHistoryIdsByAtoms = [];
        /** @var AtomHistory $history */
        foreach ($firstHistoryByAtoms as $history) {
            $arFirstHistoryIdsByAtoms[] = $history->id;
        }
        $atomsQuery->whereIn($atomHistoryTable . '.id', $arFirstHistoryIdsByAtoms);

        return $atomsQuery->get()->toArray();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, "parent_id");
    }
}
