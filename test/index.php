<?php 

	class Stoke {

		private $data;

		private $record_per_page = 10;

		public function initContructData() {
			$json_data = file_get_contents("test_21012022.json");
			$decode_data = json_decode($json_data);
			$data = [];
			foreach($decode_data as $i => $item) {
				$data[$i] = (array) $item;
			}
			return $data;
		}

		function filterA($keyWord = null, $fromPrice = null, $toPrice = null, $brand = null, $year = null, $status = 0, $page = 1, $orderBy = []) {

			$this->data = $this->initContructData();

			$this->whereNoDeleted();

			$this->filterKeyWord($keyWord);
			
			$this->filterPrice($fromPrice, $toPrice);
			
			$this->filterBrand($brand);

			$this->filterYear($year);

			$this->filterStatus($status);

			$this->orderByField($orderBy);

			list($number_page, $list_items) = $this->paginate($page);

			return array(
				'page' => $page,
				'number_page' => $number_page,
				'count_items' => count($this->data),
				'list_items' => $list_items,
			);
		}

		function filterB($brand = null, $year = null, $status = null) {

			$this->data = $this->initContructData();

			$this->filterBrand($brand);

			$this->filterYear($year);

			$this->filterStatus($status);

			$this->whereNoDeleted();

			$prices = $this->array_column('price');
			rsort($prices);


			$first = $prices[0];
			$last = $prices[count($prices) - 1];

			$lon_nhat = [];
			$nho_nhat = [];
			foreach($prices as $i => $price) {
				if($first == $price) {
					$lon_nhat[] = $this->data[$i];
				}
				if($last == $price) {
					$nho_nhat[] = $this->data[$i];
				}
			}

			return [
				'lon_nhat' => $lon_nhat,
				'nho_nhat' => $nho_nhat,
			];
		}

		private function filterKeyWord($keyWord = null) {
			if(!empty($keyWord)) {
				$input = preg_quote($keyWord, '~'); // don't forget to quote input string!
				$dataFilterdName = preg_grep('~' . $input . '~', array_column($this->data, 'name'));
				$dataFilterdBrand = preg_grep('~' . $input . '~', array_column($this->data, 'branch'));

				$result = [];
				foreach($dataFilterdName as $i => $name) {
					if(isset($this->data[$i])) {
						$result[$i] = $this->data[$i];
					}
				}
				foreach($dataFilterdBrand as $i => $name) {
					if(isset($this->data[$i])) {
						$result[$i] = $this->data[$i];
					}
				}

				$this->data = $result;
			}
		}

		private function filterPrice($fromPrice = null, $toPrice = null) {
			if(!empty($fromPrice) || !empty($toPrice)) {

				if($fromPrice < 0 || $toPrice < 0)
					return;

				$dataPrice = array_column($this->data, 'price');
				if(empty($fromPrice)) {
					$fromPrice = 0;
				}
				if(empty($toPrice)) {
					$toPrice = 0;
				}

				$result = [];
				foreach($this->data as $i => $item) {
					if($fromPrice <= $item['price'] && $toPrice >= $item['price']) {
						$result[$i] = $item;
					}
				}
				$this->data = $result;
			}
		}

		private function filterBrand($brand = null) {
			if(empty($brand))
				return;

			$paramters = [];
			if(is_string($brand)) {
				$paramters = array($brand);
			} elseif(is_array($brand)) {
				$paramters = $brand;
			}

			$brands = $this->array_column('branch');

			$result = [];
			foreach($brands as $i => $brand) {
				foreach($paramters as $paramter) {
					if($paramter == $brand) {
						$result[$i] = $this->data[$i];
					}
				}
			}
			$this->data = $result;
		}

		private function filterYear($year = null) {
			if(empty($year))
				return;

			$paramters = [];
			if(is_integer($year) || is_string($year)) {
				$paramters = array((int) $year);
			} elseif(is_array($year)) {
				$paramters = $year;
			}

			$years = $this->array_column('year');

			$result = [];
			foreach($years as $i => $year_number) {
				foreach($paramters as $paramter) {
					if($paramter == $year_number) {
						$result[$i] = $this->data[$i];
					}
				}
			}
			$this->data = $result;
		}


		private function filterStatus($status = null) {
			if(empty($status))
				return;

		
			$in_stocks = $this->array_column('in_stock');
			foreach($in_stocks as $i => $in_stock) {
				if(1 == $status) {
					if($in_stock <= 0) {
						unset($this->data[$i]);
					}
				} else {
					if($in_stock > 0) {
						unset($this->data[$i]);
					}
				}
			}
		}

		private function paginate($page) {
			$data = array_chunk($this->data, $this->record_per_page);
			if(isset($data[$page]))
				return array(count($data), $data[$page]);
		}

		private function orderByField($orderByField = []) {
			$fieldname = array_keys($orderByField)[0] ?? null;
			$sort = array_values($orderByField)[0] ?? 'ASC';
			if(empty($fieldname))
				return;

			$data_by_fields = $this->array_column($fieldname);
			if($sort == 'ASC') {
				sort($data_by_fields);
			} else {
				rsort($data_by_fields);
			}

			$result = [];
			foreach($data_by_fields as $i => $item) {
				$result[$i] = $item;
			}
			$this->data = $result;
		}

		private function whereNoDeleted() {
			foreach($this->data as $i => $item) {
				if($item['is_delete'] == true) {
					unset($this->data[$i]);
				}
			}

		}

		private function array_column($column) {
			$result = [];
			foreach($this->data as $i => $item) {
				$result[$i] = $item[$column];
			}
			return $result;
		}

	}

	$stoke = new Stoke();
	// var_dump($stoke->filterA("", 0, 0, "", 0, 1, 1, ['name' => 'ASC']));
	var_dump($stoke->filterB());
?>