<?php

namespace App\Http\Controllers;

use App\Atom;
use App\AtomHistory;
use App\Exceptions\AtomNotFoundException;
use App\Exceptions\DataMatrixCodeAllreadyExists;
use App\Exceptions\NewAtomStateAreSameAsLast;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Lumen\Http\ResponseFactory;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AtomController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Добавляем новый атом в систему
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $createdAtom = Atom::addAtom($request->all());
        } catch (DataMatrixCodeAllreadyExists $e) {
            return Response()->json(
                [
                    'message' => $e->getTraceAsString(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $e) {
            return Response()->json(
                [
                    'message' => $e->getTraceAsString(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response()->json(
            [
                'created_atom_id' => $createdAtom->id,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Добавляем новый атом в систему
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addMany(Request $request): JsonResponse
    {
        try {
            $createdAtoms = Atom::addAtoms($request->all());
        } catch (DataMatrixCodeAllreadyExists $e) {
            return Response()->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $e) {
            return Response()->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response()->json(
            [
                'atoms' => $createdAtoms,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Добавляем новое состояние
     *
     * @param Request $request
     * @param $atom_id
     * @return JsonResponse|\Illuminate\Http\Response|ResponseFactory
     */
    public function changeState(Request $request, $atom_id)
    {
        try {
            AtomHistory::addAtomState($atom_id, $request->all());
        } catch (AtomNotFoundException $exception) {
            return Response()->json(
                [
                    'message' => 'Atom with ' . $atom_id . ' id not found.',
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (NewAtomStateAreSameAsLast $exception) {
            return Response()->json(
                [
                    'message' => 'Atom place now has same position. ',
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $exception) {
            return Response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Добавляем новое состояние нескольким атомам
     *
     * @param Request $request
     * @param $atom_id
     * @return JsonResponse|\Illuminate\Http\Response|ResponseFactory
     */
    public function changeStates(Request $request)
    {
        try {
            AtomHistory::addAtomStates($request->all()['atomsToSave']);
        } catch (NewAtomStateAreSameAsLast $exception) {
            return Response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $exception) {
            return Response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Добавляем новое состояние нескольким атомам
     *
     * @param Request $request
     * @param $atom_id
     * @return JsonResponse|\Illuminate\Http\Response|ResponseFactory
     */
    public function changeFirstStates(Request $request)
    {
        try {
            Atom::changeActualAtomsPurchaseItems($request->all()['atomsToSave']);
        } catch (NewAtomStateAreSameAsLast $exception) {
            return Response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $exception) {
            return Response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Поиск товаров по нескольким расположениям на складе в ячейках
     * Список атомов, которые сейчас числятся в заданных размещениях
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findByPlacesAndAssortmentId(Request $request): JsonResponse
    {
        try {
            $requestVars = $request->all();
            if (isset($requestVars['commission'])) {
                $commission = (bool)$request->all()['commission'];
            } else {
                $commission = null;
            }
            if (isset($requestVars['requireMarkedAtoms'])) {
                $requireMarkedAtoms = (bool)$request->all()['requireMarkedAtoms'];
            } else {
                $requireMarkedAtoms = true;
            }
            if (empty($requestVars['places'])) {
                $places = null;
            } else {
                $places = $requestVars['places'];
            }
            if (isset($requestVars['assortmentId'])) {
                $assortmentId = $requestVars['assortmentId'];
            } else {
                $assortmentId = null;
            }
            if (isset($requestVars['eventsIds']) && !empty($requestVars['eventsIds'])) {
                $events = $requestVars['eventsIds'];
            } else {
                $events = null;
            }
            if (isset($requestVars['dataMatrixCodes']) && !empty($requestVars['dataMatrixCodes'])) {
                $dataMatrixCodes = $requestVars['dataMatrixCodes'];
            } else {
                $dataMatrixCodes = null;
            }
            if (isset($requestVars['purchaseInvoiceItemsIds']) && !empty($requestVars['purchaseInvoiceItemsIds'])) {
                $purchaseInvoiceItemsIds = $requestVars['purchaseInvoiceItemsIds'];
            } else {
                $purchaseInvoiceItemsIds = null;
            }
            $limit = isset($requestVars['limit']) ? (int)$requestVars['limit'] : 0;

            if (isset($requestVars['findingMode'])) {
                $findingMode = intval($requestVars['findingMode']);
            } else {
                $findingMode = 1;
            }
            $lastAtomsInfo = AtomHistory::getAtomsStates(
                $places,
                $assortmentId,
                $commission,
                $findingMode,
                $requireMarkedAtoms,
                $events,
                $dataMatrixCodes,
                $purchaseInvoiceItemsIds
            );
            if ($limit) {
                $lastAtomsInfo = array_slice($lastAtomsInfo, 0, $limit);
            }
        } catch (Throwable $exception) {
            return Response()->json(['message' => $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return Response()->json(['atoms' => $lastAtomsInfo], Response::HTTP_OK);
    }

    /**
     * @param $atom_id
     * @return JsonResponse
     */
    public function getLastAtomState($atom_id): JsonResponse
    {
        try {
            $lastAtomInfo = Atom::getLastAtomState($atom_id);
        } catch (AtomNotFoundException $exception) {
            return Response()->json(['message' => 'Atom with id ' . $atom_id . ' not found.'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $exception) {
            return Response()->json(['message' => 'Не верно введены параметры.'], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return Response()->json(['message' => $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return Response()->json($lastAtomInfo, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAtomStatesByIds(Request $request): JsonResponse
    {
        try {
            $params = $request->all();
            if (!isset($params['ids'])) {
                throw new InvalidArgumentException();
            }
            $atoms = Atom::getLastAtomStates($params['ids']);
        } catch (InvalidArgumentException $exception) {
            return Response()->json(['message' => 'Не верно введены параметры.'], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return Response()->json(['message' => $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return Response()->json(['atoms' => $atoms], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFirstAtomStates(Request $request): JsonResponse
    {
        try {
            $requestVars = $request->all();
            if (empty($requestVars['atomIDs'])) {
                throw new Exception('Не заданы id атомов');
            }

            $atoms = AtomHistory::getFirstAtomStates($requestVars['atomIDs']);
        } catch (InvalidArgumentException $exception) {
            return Response()->json(['message' => 'Не верно введены параметры.'], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return Response()->json(['message' => $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return Response()->json(['atoms' => $atoms], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAtomsByMatrixCodes(Request $request): JsonResponse
    {
        try {
            $requestVars = $request->all();
            if (empty($requestVars['matrixCodes'])) {
                throw new Exception('Не заданы matrix коды');
            }

            $atoms = Atom::getByMatrixCodes($requestVars['matrixCodes']);
        } catch (InvalidArgumentException $exception) {
            return Response()->json(['message' => 'Не верно введены параметры.'], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return Response()->json(['message' => $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return Response()->json(['atoms' => $atoms], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function returnMany(Request $request): JsonResponse
    {
        try {
            $requestVars = $request->all();
            if (empty($requestVars['atomsToReturn'])) {
                throw new Exception('Не заданы атомы');
            }
            $atoms = $requestVars['atomsToReturn'];

            $atomInfo = AtomHistory::returnAtomsToPreviousPlace($atoms);
        } catch (AtomNotFoundException $e) {
            return Response()->json(
                [
                    'message' => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return Response()->json(
                [
                    'message' => 'Не верно введены параметры.',
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Throwable $e) {
            return Response()->json(
                [
                    'message' => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response()->json(
            [
                'atoms' => $atomInfo,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @param int $atom_id
     * @throws Exception
     */
    public function deleteByAtomId(int $atom_id)
    {
        Atom::deleteOne($atom_id);
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function deleteByAtomIds(Request $request)
    {
        Atom::deleteMany($request->all()['ids']);
    }

}
