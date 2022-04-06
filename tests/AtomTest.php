<?php

use App\Atom;
use App\AtomHistory;

class AtomTest extends TestCase
{
	/**
	 * @var Atom[] $atomList
	**/
	private $atomList = [];

	public function tearDown(): void
	{
		foreach ($this->atomList as $item) {
			$item->delete();
		}
		parent::tearDown();
	}

	/**
     * A basic test example.
     *
     * @return void
     */
    public function testAtomSimple()
    {
    	$matrixCode1 = 'etwtewtwtewt';
		$matrixCode2 = 'etwtewtwtewt333';
    	$data1 = [
    		'admin_id' => 1,
			'place_type' => 1,
			'place_id' => 1,
			'data_matrix_code' => $matrixCode1,
			'assortment_id' => 48,
			'commission' => false,
			'expired_at' => null
		];
		$data3 = [
			'admin_id' => 1,
			'place_type' => 1,
			'place_id' => 1,
			'data_matrix_code' => $matrixCode2,
			'assortment_id' => 48,
			'commission' => false,
			'expired_at' => null
		];
    	$atom = Atom::addAtom($data1);
		$atom2 = Atom::addAtom($data3);

    	// Проверяем что атом создался
		$this->assertNotNull($atom, 'not exist new Atom');
		$this->assertNotNull($atom2, 'not exist new Atom2');

    	$this->atomList[] = $atom;
		$this->atomList[] = $atom2;


		// Проверяем что у атома есть id
		$this->assertNotNull($atom->id, 'not exist new Atom id');
		// Проверяем что у атома id целое число
		$this->assertIsInt($atom->id);

		//Проверяем текущее состояние атома
		$lastInfo = AtomHistory::getLastAtomInfo($atom->id);
		$this->assertNotNull($lastInfo, 'not exist atom by id');
		$this->assertEquals($atom->id, $lastInfo->atom_id, 'atom id not equal');
		$this->assertEquals(1, $lastInfo->place_id, 'atom state place id not equal');
		$this->assertEquals(1, $lastInfo->place_type, 'atom state place type not equal');
		$this->assertEquals(1, $lastInfo->admin_id, 'atom state admin not equal');
		$this->assertEquals(48, $lastInfo->assortment_id, 'atom state place type not equal');
		$this->assertEquals($matrixCode1, $lastInfo->data_matrix_code, 'atom state place type not equal');

		// Получаем список атомов в типе размещения 1 с id 1
		$place = [
			'placeType' => 1,
			'placeId' => 1
		];
		$result = AtomHistory::getAtomsStates([$place], 48);

		// Проверяем что результат не пустой
		$this->assertNotNull($result, 'not exist atom in place_type 1, place_id 1');
		$this->assertEquals(2, count($result), 'по двум атомам не 2 результата');
		$atomSate = $result[0];
		// Проверяем что id нашего тестового атома совпадает с id атома в результате
		$this->assertEquals($atom->id, $atomSate->atom_id);
		$this->assertEquals(1, $atomSate->admin_id, 'atom state by place admin not equal');
		$this->assertEquals(48, $atomSate->assortment_id, 'atom state by place place type not equal');
		$this->assertEquals($matrixCode1, $atomSate->data_matrix_code, 'atom state by place place type not equal');

		// Перемещаем атом1 в тип размещения 3 с id 2 и adminId 4
		$data2 = [
			'admin_id' => 4,
			'place_type' => 3,
			'commission' => false,
			'place_id' => 2,
		];

		AtomHistory::addAtomState($atom->id, $data2);

		//Проверяем текущее состояние атома после перемещения
		$lastInfo = AtomHistory::getLastAtomInfo($atom->id);
		$this->assertNotNull($lastInfo, 'not exist atom by id after change place');
		$this->assertEquals($atom->id, $lastInfo->atom_id, 'atom id not equal after change place');
		$this->assertEquals(4, $lastInfo->admin_id, 'atom state admin not equal after change place');
		$this->assertEquals(2, $lastInfo->place_id, 'atom state place id not equal after change place');
		$this->assertEquals(3, $lastInfo->place_type, 'atom state place type not equal after change place');
		$this->assertEquals(48, $lastInfo->assortment_id, 'atom state place type not equal after change place');
		$this->assertEquals($matrixCode1, $lastInfo->data_matrix_code, 'atom state place type not equal after change place');


		// Получаем список атомов в типе размещения 3 с id 2
		$place = [
			'placeType' => 3,
			'placeId' => 2
		];
		$result = AtomHistory::getAtomsStates([$place], 48);
		// Проверяем что результат не пустой
		$this->assertNotNull($result, 'not exist atom in place_type 3, place_id 2');
		$this->assertEquals(1, count($result), 'в place_type 3, place_id 2 должен числится 1 атом');

		// Проверяем что id нашего тестового атома совпадает с id атома в результате
		$atomSate = $result[0];
		$this->assertEquals($atomSate->atom_id, $atom->id);

		// Получаем список атомов в типе размещения 1 с id 1
		$place = [
			'placeType' => 1,
			'placeId' => 1
		];
		$result = AtomHistory::getAtomsStates([$place], 48);
		// Проверяем что результат не пустой
		$this->assertNotNull($result, 'not exist atom in place_type 1, place_id 1');
		$this->assertEquals(1, count($result), 'в place_type 1, place_id 1 должен числится 1 атом');
		// Проверяем что id нашего тестового атома совпадает с id атома в результате
		$atomSate2 = $result[0];
		$this->assertEquals($atomSate2->atom_id, $atom2->id);
    }
}
