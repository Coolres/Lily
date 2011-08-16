<?php

abstract class Lily_Data_Mapper_Thrift 
	extends Lily_Data_Mapper_Abstract
{
	protected $schema;
	protected $client;


	protected function __construct($args) {
		if (isset($args['client'])) {
			$this->client = $args['client'];
		} else {
			$e = new Lily_Data_Mapper_Exception(__CLASS__ . " requires a thrift client to be specified initialization");
			Lily_Log::error($e->getMessage(), $e->getTrace());
			throw $e;
		}

		if (isset($args['schema'])) {
			$this->schema = $args['schema'];
			if (isset($this->schema['table'])) {
				$this->table = $this->schema['table'];
			}
		} else {
			throw new Lily_Data_Mapper_Exception("No schema specified");
		}
		$errors = null;
		if (!$this->validateSchema($this->schema, $errors)) {
			throw new Lily_Data_Mapper_Exception(print_r($errors, true));
		}
		parent::__construct($args);
	}

	/**
	 * Truncates the specified table
	 * @return boolean
	 */
	public function truncate() {
		$table_names = $this->client->getTableNames();
		if (!in_array($this->table, $table_names)) {
			return false;
		}
		if ($this->client->isTableEnabled($this->table)) {
			$this->client->disableTable($this->table);
		}
		$this->client->deleteTable($this->table);
		$this->create();
	}

	/**
	 * Deletes the specified table
	 * @return boolean
	 */
	public function create() {
		$table_names = $this->client->getTableNames();
		if (in_array($this->table, $table_names)) {
			return false;
		}

		$descriptors = array();
		foreach ($this->schema['families'] as $name => $props) {
			$temp = $props['descriptors'];
			if (!isset($temp['name'])) {
				$temp['name'] = $name;
			}
			$descriptors[] = new ColumnDescriptor($temp);
		}
		$this->client->createTable($this->table, $descriptors);
	}

	/**
	 * Get Table Name
	 */
	public function getTableName() {
		return $this->table;
	}

	/**
	 * Given a row result, will build out the model
	 * @param TRowResult $row
	 * @param unknown_type $model
	 * @throws Mapper_Exception
	 * @return Ambiguous
	 */
	public function buildModel(TRowResult& $row, &$model = null) {
		if (null === $model) $model = new $this->model();
		$this->_setRowId($row->row, $model);
		foreach ($row->columns as $name => $payload) {
			$pos = strpos($name, ':');
			$family_name = substr($name, 0, $pos);
			$column_name = substr($name, $pos+1);

			$props = $this->schema['families'][$family_name];
			// Family populate method
			if (isset($props['populate_method'])) {
				$type = isset($props['type']) ? $props['type'] : 'string';
				$method = $props['populate_method'];
				$this->$method($column_name,
					$this->convertRawToType($payload->value, $type), $model);
			// Use the family name as the property
			} elseif (empty($column_name)) {
				$type = isset($props['type']) ? $props['type'] : 'string';
				$method = Utility::toCamelCase('set_' . $family_name);
				if (method_exists($model, $method)) {
					$model->$method($this->convertRawToType($payload->value, $type));
				}
			// Column specified and found
			} elseif (isset($props['columns']) && isset($props['columns'][$column_name])) {
				$column_props = $props['columns'][$column_name];
				$type = isset($column_props['type']) ? $column_props['type'] : 'string';
				$value = $this->convertRawToType($payload->value, $type);
				if (isset($column_props['populate_method'])) {
					$method = $column_props['populate_method'];
					$this->$method($column_name, $value, $model);
				} else {

					$method1 = Utility::toCamelCase('set_' . $family_name .'_' . $column_name);
					$method2 = Utility::toCamelCase('set_' . $column_name);
					if (method_exists($model, $method1)) {
						$model->$method1($value);
					} elseif (method_exists($model, $method2)) {
						$model->$method2($value);
					}
				}
			}
		}
		return $model;
	}

	/**
	 * get
	 * @param string|string[] $row_id
	 * @return Ambigous <NULL, Ambigous, multitype:, Ambiguous, Model, unknown>
	 */
	public function get($row_id, $columns = null) {
		$pop = false;
		if (!is_array($row_id)) {
			$row_id = array($row_id);
			$pop = true;
		}
		$result = $this->_batchGet($row_id, $columns);
		if ($pop) {
			$t = reset($result);
			return $t === false ? null : $t;
		};
		return $result;
	}

	/**
	 * getByModel
	 *
	 * @param Model_Abstract $model
	 * @return Ambigous <NULL, Ambiguous, Model, unknown>
	 */
	public function getByModel(Lily_Data_Model_Abstract& $model, $columns = null)
	{
		$result = null;
		$row_id = $this->_buildRowId($model);
		if (empty($row_id)) return null;
		return $this->get($row_id, $columns);
	}

	public function getCellByModel(Lily_Data_Model_Abstract& $model, $cell) {
		$result = null;
		$row_id = $this->_buildRowId($model);
		if (empty($row_id)) return null;
		$temp = $this->client->getRowWithColumns($this->table, $row_id, array($cell));
		if (empty($temp)) return null;
		$temp = current($temp);
		return $temp->columns[$cell]->value;
	}
	
	/**
	 * insert
	 * @param Model|Model[] $model
	 * @return boolean
	 */
	public function insert($model) {
		$batch_mutations = $this->_buildTableMutations($model);
		if (empty($batch_mutations)) return false;
		$this->_batchMutate($batch_mutations);

		if ( is_array( $model ) ) {
			foreach ($model as $m) $this->invalidate( $m );
		} else {
			$this->invalidate( $model );
		}
		return true;
	}

	/**
	 * insertIgnore
	 * Inserts rows if the row does not exist
	 * @param unknown_type $model
	 * @return boolean
	 */
	public function insertIgnore($model) {
		// Need to check if the message exists first, dont want to overwrite.
		if (!is_array($model)) {
			$model = array($model);
		}

		$ids = array();
		foreach ($model as $m) {
			$id = $this->_buildRowId($m);
			$ids[$id] = $id;
		}

		// Filter out any messages that have already been added
		$result = $this->get($ids);
		foreach ($model as $id => $m) {
			$rowid = $this->_buildRowId($m);
			if (isset($result[$rowid]) && null !== $result[$rowid]) {
				unset ($model[$id]);
			}
		}

		$batch_mutations = $this->_buildTableMutations($model);
		$temp = $this->_batchMutate($batch_mutations);
		foreach ($model as $m) {
			$this->invalidate($m);
		}
		return $temp;
	}

	/**
	 * delete
	 *
	 * @param Lily_Data_Model_Abstract|Lily_Data_Model_Abstract{} $model
	 */
	public function delete($model)
	{
		if ($model instanceof Lily_Data_Model_Abstract) {
			$model = array($model);
		}
		foreach ($model as $m) {
			$row_id = $this->_buildRowId($m);
			$this->client->deleteAllRow($this->table, $row_id);
			$this->invalidate($m);
		}
		return true;
	}

	/**
	 * increment
	 *
	 * @param Model_Abstract $model
	 * @param unknown_type $column
	 * @param unknown_type $amount
	 * @return NULL
	 */
	public function increment(Lily_Data_Model_Abstract& $model, $column, $amount=1)
	{
		$row_id = $this->_buildRowId($model);
		$result = $this->client->atomicIncrement(
			$this->table, $row_id, $column, $amount
		);
		
		if ($this->cache_enabled) {
			$key = $this->_buildCacheId($row_id, array($column));
			$mc = Lily_Memcached_Manager::get();
			$mc->set($key, $result);
		}
		return $result;
	}

	/**
	 * decrement
	 *
	 * @param Model_Abstract $model
	 * @param unknown_type $column
	 * @param unknown_type $amount
	 * @return NULL
	 */
	public function decrement(Lily_Data_Model_Abstract& $model, $column, $amount=1)
	{
		if ($amount > 0) $amount = $amount * -1;
		$row_id = $this->_buildRowId($model);
		$result = $this->client->atomicIncrement(
			$this->table, $row_id, $column, $amount
		);
		
		if ($this->cache_enabled) {
			$key = $this->_buildCacheId($row_id, array($column));
			$mc = Lily_Memcached_Manager::get();
			$mc->set($key, $result);
		}
		return $result;
	}

	/**
	 * getCounter
	 *
	 * @param Model_Abstract $model
	 * @param unknown_type $column
	 * @return Ambigous <number, number>|NULL
	 */
	public function getCounter(Lily_Data_Model_Abstract& $model, $column)
	{
		$row_id = $this->_buildRowId($model);
		
		if ($this->cache_enabled) {
			$mc = Lily_Memcached_Manager::get();
			$key = $this->_buildCacheId($row_id, array($column));
			$temp = $mc->get($key);
			if ($temp) return $temp;
		}
		
		$result = $this->client->get($this->table, $row_id, $column);
		$return = 0;
		if (!empty($result)) {
			$row = reset($result);
			$return = $this->convertBinToInt($row->value);
		}
		
		if ($this->cache_enabled) {
			$mc->set($key, $return);
		}
		
		return $return;
	}

	/**
	 * setCounter
	 *
	 * @param Model_Abstract $model
	 * @param unknown_type $column
	 * @param unknown_type $value
	 */
	public function setCounter(Lily_Data_Model_Abstract& $model, $column, $value)
	{
		$row = $this->_buildRowId($model);
		$mutation = new Mutation(array(
			'column'	=> $column,
			'value'		=> $this->convertIntToBin($value)
		));
		return $this->client->mutateRow($this->table,$row,array($mutation));
	}

	/**
	 * convertBinToInt
	 *
	 * @param bin $value
	 * @return number
	 */
	public function convertBinToInt($value)
	{
		$temp = unpack('N2', $value);
		return (($temp[1] & 0xFFFFFFFF) << 32) + ($temp[2] & 0xFFFFFFFF);
	}

	/**
	 * convertIntToBin
	 *
	 * @param int $value
	 */
	public function convertIntToBin($value)
	{
		$result = pack('N', 0) . pack('N', $value);
		return $result;
	}

	/**
	 * buildTableMutations
	 * @param Model $model
	 * @param BatchMutation[] $batch_mutations
	 * @return Ambigous <multitype:, BatchMutation>
	 */
	protected function _buildTableMutations($model, array& $batch_mutations=null) {
		if (!is_array($model)) {
			$model = array($model);
		}
		if (empty($model)) return null;

		if (null === $batch_mutations) $batch_mutations = array();

		foreach ($model as $m) {
			if (null === $m) continue; // Skip null models. This shouldnt really exist need to figure out why this happened.
			$mutations = null;
			$this->_buildRowMutations($m, $mutations);
			$batch_mutations[] = new BatchMutation(array(
				'row'		=> $this->_buildRowId($m),
				'mutations'	=> $mutations
			));
		}
		return $batch_mutations;
	}

	/**
	 * buildRowMutations
	 * @param Model $model
	 * @param Mutation[] $mutations
	 * @throws Exception
	 * @return Ambigous <multitype:, Mutation>
	 */
	protected function _buildRowMutations(Lily_Data_Model_Abstract& $model, array& $mutations=null) {
		if (!$model instanceof $this->model) {
			throw new Exception('Specified model is not instance of ' . $this->model);
		}

		if (null === $mutations) $mutations = array();

		$families = $this->schema['families'];
		foreach ($families as $family_name => $meta) {
			// Skip over families marked to ignore
			if (isset($meta['mutate_ignore']) && $meta['mutate_ignore']) continue;

			if (isset($meta['mutate_method'])) {
				$method = $meta['mutate_method'];
				$this->$method($model, $mutations);
			} elseif (isset($meta['columns']) && !empty($meta['columns'])) {
				foreach ($meta['columns'] as $column_name => $column_props) {
					// SKip over columns marked to ignore
					if (isset($column_props['mutate_ignore']) && $column_props['mutate_ignore']) continue;
					// Mapper method override set, invoke overridding method
					if (isset($column_props['mutate_method'])) {
						$method = $column_props['mutate_method'];
						$this->$method($model, $mutations);
					} else {// Use the conventional mutation building approach
						$mutation = $this->_buildMutation($model, $family_name, $column_name, $column_props);
						if (null !== $mutation) $mutations[] = $mutation;
					}
				}
			} else {
				$mutation = $this->_buildMutation($model, $family_name, '', $meta);
				if (null !== $mutation) $mutations[] = $mutation;
			}
		}
		return $mutations;
	}

	/**
	 * _mutate
	 *
	 * @param unknown_type $row_id
	 * @param unknown_type $mutations
	 * @return boolean
	 */
	protected function _mutate($row_id, $mutations) {
		if (empty($mutations)) return false;
		return $this->client->mutateRow($this->table, $row_id, $mutations);
	}

	/**
	 * batchMutate
	 * @param Mutation[] $mutations
	 * @return boolean
	 */
	protected function _batchMutate($mutations) {
	    if (empty($mutations)) return false;
		$result =  $this->client->mutateRows($this->table, $mutations);
		return $result;
	}

	/**
	 * batchGet
	 * Retrieves models associated with the specified row ids
	 * TODO: Need to implement using pcntl fork for better multi-get
	 * @param array $row_ids
	 * @return NULL|Ambigous <multitype:, Ambiguous, Model, unknown>
	 */
	protected function _batchGet(array $row_ids, $columns = null) {
		if (empty($row_ids)) return null;
		$result = array();
		foreach ($row_ids as $row_id) {
			if ( $model = $this->cacheGet($row_id, $columns) ) {
				$result[$row_id] = $model;
				continue;
			}

			if (empty($columns)) {
				$row_result = $this->client->getRow($this->table, $row_id);
			} else {
				$row_result = $this->client->getRowWithColumns($this->table, $row_id, $columns);
			}

			if (!empty($row_result)) {
				$temp = $this->buildModel(current($row_result));
				$result[$row_id] = $temp;
				$this->cacheSet($temp, $columns);
			}
		}
		return $result;
	}


	/**
	 * convertRawToType
	 * Given the 'raw' (as in raw after the Thrift client processes it) do any conversions
	 * necessary to cast it to the type specified in schema.
	 * @param Abigous $raw_value
	 * @param string $type
	 * @return Ambiguous
	 */
	private function convertRawToType($raw_value, $type) {
		switch ($type) {
			case 'BININT':
				$value = $this->convertBinToInt($raw_value);
			break;

			default:
				$value = $raw_value;
			break;
		}
		return $value;
	}

	/**
	 * Convert upper level php processing to format necessary.
	 * Most oftion to convert an int to its binary form for atomic fields.
	 * @param AMbigous $processed
	 * @param string $type
	 * @return Ambiguous
	 */
	private function convertTypeToRaw($processed, $type) {
		switch ($type) {
			case 'BININT':
				$value = $this->convertIntToBin($processed);
			break;

			default:
				$value = $processed;
			break;
		}
		return $value;
	}

	/**
	 * Build mutations for all properties set on the specified object
	 * according to rules set forth in the schema.
	 * @param Model_Abstract $model
	 * @param string $family
	 * @param string $column
	 * @param schema_props $props
	 * @return NULL|Ambigous <NULL, Mutation>
	 */
	private function _buildMutation(Lily_Data_Model_Abstract& $model, $family, $column, $props)
	{
		if (isset($props['mutate_ignore']) && $props['mutate_ignore']) {
			return null;
		}

		$mutation = null;
		$method_name = null;
		$cell_name = $family . ':' . $column;
		if (empty($column)) {
			$method_name = Utility::toCamelCase('get_' . $family);
			if (!method_exists($model, $method_name)) $method_name = null;
		} else {
			if (isset($props['model_property'])) {
				$property = $props['model_property'];
				$method_name1 = Utility::toCamelCase('get_' . $family . '_' . $property);
				$method_name2 = Utility::toCamelCase('get_' . $property) ;
			} else {
				$method_name1 = Utility::toCamelCase('get_' . $family . '_' . $column);
				$method_name2 = Utility::toCamelCase('get_' . $column) ;
			}
			if (method_exists($model, $method_name1)) $method_name = $method_name1;
			if (method_exists($model, $method_name2)) $method_name = $method_name2;
		}

		if (!empty($method_name)) {
			$value = $model->$method_name();
			if (null !== $value) {
				$type = isset($props['type']) ? $props['type'] : 'string';
				switch ($type) {
					case 'BININT' :
						$value = $this->convertIntToBin($value);
					break;

					default: break;
				}
				$mutation = new Mutation(array(
					'column'	=> $cell_name,
					'value'		=> $value
				));
			}
		}
		return $mutation;
	}

	/**
	 * Validate the schema to see if it complies with some syntactical/semantical guidelines.
	 * @param array $schema
	 * @param & $errors
	 * @return boolean
	 */
	private function validateSchema($schema, &$errors) {
		if (null === $errors) $errors = array();
		if (!isset($schema['table'])) {
			$errors[] = "table undefined";
		}

		if (!isset($schema['families']) || empty($schema['families'])) {
			$errors[] = "families undefined";
		}

		foreach ($schema['families'] as $name => $props) {
			if (!isset($props['descriptors'])) {
				$errors[] = "descriptors for family $name undefined";
			}

			if (isset($props['dynamic']) && $props['dynamic']) {
				$method = @$props['mutate_method'];
				if (empty($method)) {
					$errors[] = "dynamic family, $name, mutate_method undefined";
				} elseif (!method_exists($this, $method)) {
					$errors[] = "$method method does not exist";
				}

				$method = @$props['populate_method'];
				if (empty($method)) {
					$errors[] = "dynamic family, $name, populate_method undefined";
				} elseif (!method_exists($this, $method)) {
					$errors[] = "$method method does not exist";
				}
			} else {
//				if (empty($props['columns'])) {
//					$errors[] = "non dynamic family does not have any columns defined";
//				}
			}
		}
		return count($errors) > 0 ? false : true;
	}

	/* (non-PHPdoc)
	 * @see Mapper_Abstract::_buildId()
	 */
	protected function _buildId(Lily_Data_Model_Abstract& $model) {
		return $this->_buildRowId($model);
	}

	/**
	 * setRowId
	 *
	 * @param unknown_type $id
	 * @param Model_Abstract $model
	 */
	abstract protected function _setRowId($id, Lily_Data_Model_Abstract& $model);

	/**
	 * getRowId
	 *
	 * @param unknown_type $model
	 */
	abstract protected function _buildRowId(Lily_Data_Model_Abstract& $model);


}