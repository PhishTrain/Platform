<?php

/**
 * Autogenerated base class representing sent rows
 * in the Streams database.
 *
 * Don't change this file, since it can be overwritten.
 * Instead, change the Streams_Sent.php file.
 *
 * @module Streams
 */
/**
 * Base class representing 'Sent' rows in the 'Streams' database
 * @class Base_Streams_Sent
 * @extends Db_Row
 *
 * @param {array} [$fields=array()] The fields values to initialize table row as 
 * an associative array of $column => $value pairs
 * @param {string} [$fields.publisherId] defaults to ""
 * @param {string} [$fields.streamName] defaults to ""
 * @param {string|Db_Expression} [$fields.insertedTime] defaults to new Db_Expression("CURRENT_TIMESTAMP")
 * @param {string|Db_Expression} [$fields.sentTime] defaults to null
 * @param {string} [$fields.byUserId] defaults to ""
 * @param {string} [$fields.comment] defaults to ""
 * @param {string} [$fields.instructions] defaults to null
 * @param {string} [$fields.chatPublisherId] defaults to ""
 * @param {string} [$fields.chatStreamName] defaults to null
 */
abstract class Base_Streams_Sent extends Db_Row
{
	/**
	 * @property $publisherId
	 * @type string
	 * @default ""
	 * id of user that publishes the stream to place the message on
	 */
	/**
	 * @property $streamName
	 * @type string
	 * @default ""
	 * the stream to place the message on
	 */
	/**
	 * @property $insertedTime
	 * @type string|Db_Expression
	 * @default new Db_Expression("CURRENT_TIMESTAMP")
	 * saved on shard of publisherId
	 */
	/**
	 * @property $sentTime
	 * @type string|Db_Expression
	 * @default null
	 * time on shard of byUserId
	 */
	/**
	 * @property $byUserId
	 * @type string
	 * @default ""
	 * id of the user sending the message.
	 */
	/**
	 * @property $comment
	 * @type string
	 * @default ""
	 * short human-readable content, like twitter and its 140 characters
	 */
	/**
	 * @property $instructions
	 * @type string
	 * @default null
	 * instructions are in a machine-readable format depending on the type of the stream. delta, reverse delta, linking and embedding
	 */
	/**
	 * @property $chatPublisherId
	 * @type string
	 * @default ""
	 * If not 0, id of user that publishes the chat stream
	 */
	/**
	 * @property $chatStreamName
	 * @type string
	 * @default null
	 * The name of the stream where people can chat about this message
	 */
	/**
	 * The setUp() method is called the first time
	 * an object of this class is constructed.
	 * @method setUp
	 */
	function setUp()
	{
		$this->setDb(self::db());
		$this->setTable(self::table());
		$this->setPrimaryKey(
			array (
			)
		);
	}

	/**
	 * Connects to database
	 * @method db
	 * @static
	 * @return {iDb} The database object
	 */
	static function db()
	{
		return Db::connect('Streams');
	}

	/**
	 * Retrieve the table name to use in SQL statement
	 * @method table
	 * @static
	 * @param {boolean} [$with_db_name=true] Indicates wheather table name should contain the database name
 	 * @return {string|Db_Expression} The table name as string optionally without database name if no table sharding
	 * was started or Db_Expression class with prefix and database name templates is table was sharded
	 */
	static function table($with_db_name = true)
	{
		if (Q_Config::get('Db', 'connections', 'Streams', 'indexes', 'Sent', false)) {
			return new Db_Expression(($with_db_name ? '{$dbname}.' : '').'{$prefix}'.'sent');
		} else {
			$conn = Db::getConnection('Streams');
  			$prefix = empty($conn['prefix']) ? '' : $conn['prefix'];
  			$table_name = $prefix . 'sent';
  			if (!$with_db_name)
  				return $table_name;
  			$db = Db::connect('Streams');
  			return $db->dbName().'.'.$table_name;
		}
	}
	/**
	 * The connection name for the class
	 * @method connectionName
	 * @static
	 * @return {string} The name of the connection
	 */
	static function connectionName()
	{
		return 'Streams';
	}

	/**
	 * Create SELECT query to the class table
	 * @method select
	 * @static
	 * @param {string|array} [$fields='*'] The fields as strings, or array of alias=>field
	 * @param {string|array} [$alias=null] The tables as strings, or array of alias=>table
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function select($fields, $alias = null)
	{
		if (!isset($alias)) $alias = '';
		$q = self::db()->select($fields, self::table().' '.$alias);
		$q->className = 'Streams_Sent';
		return $q;
	}

	/**
	 * Create UPDATE query to the class table
	 * @method update
	 * @static
	 * @param {string} [$alias=null] Table alias
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function update($alias = null)
	{
		if (!isset($alias)) $alias = '';
		$q = self::db()->update(self::table().' '.$alias);
		$q->className = 'Streams_Sent';
		return $q;
	}

	/**
	 * Create DELETE query to the class table
	 * @method delete
	 * @static
	 * @param {object} [$table_using=null] If set, adds a USING clause with this table
	 * @param {string} [$alias=null] Table alias
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function delete($table_using = null, $alias = null)
	{
		if (!isset($alias)) $alias = '';
		$q = self::db()->delete(self::table().' '.$alias, $table_using);
		$q->className = 'Streams_Sent';
		return $q;
	}

	/**
	 * Create INSERT query to the class table
	 * @method insert
	 * @static
	 * @param {object} [$fields=array()] The fields as an associative array of column => value pairs
	 * @param {string} [$alias=null] Table alias
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function insert($fields = array(), $alias = null)
	{
		if (!isset($alias)) $alias = '';
		$q = self::db()->insert(self::table().' '.$alias, $fields);
		$q->className = 'Streams_Sent';
		return $q;
	}
	
	/**
	 * Inserts multiple rows into a single table, preparing the statement only once,
	 * and executes all the queries.
	 * @method insertManyAndExecute
	 * @static
	 * @param {array} [$rows=array()] The array of rows to insert. 
	 * (The field names for the prepared statement are taken from the first row.)
	 * You cannot use Db_Expression objects here, because the function binds all parameters with PDO.
	 * @param {array} [$options=array()]
	 *   An associative array of options, including:
	 *
	 * * "chunkSize" {integer} The number of rows to insert at a time. defaults to 20.<br/>
	 * * "onDuplicateKeyUpdate" {array} You can put an array of fieldname => value pairs here,
	 * 		which will add an ON DUPLICATE KEY UPDATE clause to the query.
	 *
	 */
	static function insertManyAndExecute($rows = array(), $options = array())
	{
		self::db()->insertManyAndExecute(
			self::table(), $rows,
			array_merge($options, array('className' => 'Streams_Sent'))
		);
	}
	
	/**
	 * Create raw query with begin clause
	 * You'll have to specify shards yourself when calling execute().
	 * @method begin
	 * @static
	 * @param {string} [$lockType=null] First parameter to pass to query->begin() function
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function begin($lockType = null)
	{
		$q = self::db()->rawQuery('')->begin($lockType);
		$q->className = 'Streams_Sent';
		return $q;
	}
	
	/**
	 * Create raw query with commit clause
	 * You'll have to specify shards yourself when calling execute().
	 * @method commit
	 * @static
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function commit()
	{
		$q = self::db()->rawQuery('')->commit();
		$q->className = 'Streams_Sent';
		return $q;
	}
	
	/**
	 * Create raw query with rollback clause
	 * @method rollback
	 * @static
	 * @param {array} $criteria Can be used to target the rollback to some shards.
	 *  Otherwise you'll have to specify shards yourself when calling execute().
	 * @return {Db_Query_Mysql} The generated query
	 */
	static function rollback()
	{
		$q = self::db()->rawQuery('')->rollback();
		$q->className = 'Streams_Sent';
		return $q;
	}
	
	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_publisherId
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_publisherId($value)
	{
		if (!isset($value)) {
			$value='';
		}
		if ($value instanceof Db_Expression) {
			return array('publisherId', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".publisherId");
		if (strlen($value) > 31)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".publisherId");
		return array('publisherId', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the publisherId field
	 * @return {integer}
	 */
	function maxSize_publisherId()
	{

		return 31;			
	}

	/**
	 * Returns schema information for publisherId column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_publisherId()
	{

return array (
  0 => 
  array (
    0 => 'varbinary',
    1 => '31',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => 'MUL',
  3 => '',
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_streamName
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_streamName($value)
	{
		if (!isset($value)) {
			$value='';
		}
		if ($value instanceof Db_Expression) {
			return array('streamName', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".streamName");
		if (strlen($value) > 255)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".streamName");
		return array('streamName', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the streamName field
	 * @return {integer}
	 */
	function maxSize_streamName()
	{

		return 255;			
	}

	/**
	 * Returns schema information for streamName column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_streamName()
	{

return array (
  0 => 
  array (
    0 => 'varbinary',
    1 => '255',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => '',
  3 => NULL,
);			
	}

	/**
	 * Method is called before setting the field and normalize the DateTime string
	 * @method beforeSet_insertedTime
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value does not represent valid DateTime
	 */
	function beforeSet_insertedTime($value)
	{
		if ($value instanceof Db_Expression) {
			return array('insertedTime', $value);
		}
		if ($value instanceof DateTime) {
			$value = $value->getTimestamp();
		}
		if (is_numeric($value)) {
			$newDatetime = new DateTime();
			$datetime = $newDateTime->setTimestamp($value);
		} else {
			$datetime = new DateTime($value);
		}
		$value = $datetime->format("Y-m-d h:i:s");
		return array('insertedTime', $value);			
	}

	/**
	 * Returns schema information for insertedTime column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_insertedTime()
	{

return array (
  0 => 
  array (
    0 => 'timestamp',
    1 => '255',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => 'MUL',
  3 => 'CURRENT_TIMESTAMP',
);			
	}

	/**
	 * Method is called before setting the field and normalize the DateTime string
	 * @method beforeSet_sentTime
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value does not represent valid DateTime
	 */
	function beforeSet_sentTime($value)
	{
		if (!isset($value)) {
			return array('sentTime', $value);
		}
		if ($value instanceof Db_Expression) {
			return array('sentTime', $value);
		}
		if ($value instanceof DateTime) {
			$value = $value->getTimestamp();
		}
		if (is_numeric($value)) {
			$newDatetime = new DateTime();
			$datetime = $newDateTime->setTimestamp($value);
		} else {
			$datetime = new DateTime($value);
		}
		$value = $datetime->format("Y-m-d h:i:s");
		return array('sentTime', $value);			
	}

	/**
	 * Returns schema information for sentTime column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_sentTime()
	{

return array (
  0 => 
  array (
    0 => 'timestamp',
    1 => '255',
    2 => '',
    3 => false,
  ),
  1 => true,
  2 => 'MUL',
  3 => NULL,
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_byUserId
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_byUserId($value)
	{
		if (!isset($value)) {
			$value='';
		}
		if ($value instanceof Db_Expression) {
			return array('byUserId', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".byUserId");
		if (strlen($value) > 31)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".byUserId");
		return array('byUserId', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the byUserId field
	 * @return {integer}
	 */
	function maxSize_byUserId()
	{

		return 31;			
	}

	/**
	 * Returns schema information for byUserId column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_byUserId()
	{

return array (
  0 => 
  array (
    0 => 'varbinary',
    1 => '31',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => 'MUL',
  3 => NULL,
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_comment
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_comment($value)
	{
		if (!isset($value)) {
			$value='';
		}
		if ($value instanceof Db_Expression) {
			return array('comment', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".comment");
		if (strlen($value) > 255)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".comment");
		return array('comment', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the comment field
	 * @return {integer}
	 */
	function maxSize_comment()
	{

		return 255;			
	}

	/**
	 * Returns schema information for comment column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_comment()
	{

return array (
  0 => 
  array (
    0 => 'varchar',
    1 => '255',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => '',
  3 => NULL,
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_instructions
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_instructions($value)
	{
		if (!isset($value)) {
			return array('instructions', $value);
		}
		if ($value instanceof Db_Expression) {
			return array('instructions', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".instructions");
		if (strlen($value) > 4092)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".instructions");
		return array('instructions', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the instructions field
	 * @return {integer}
	 */
	function maxSize_instructions()
	{

		return 4092;			
	}

	/**
	 * Returns schema information for instructions column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_instructions()
	{

return array (
  0 => 
  array (
    0 => 'varchar',
    1 => '4092',
    2 => '',
    3 => false,
  ),
  1 => true,
  2 => '',
  3 => NULL,
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_chatPublisherId
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_chatPublisherId($value)
	{
		if (!isset($value)) {
			$value='';
		}
		if ($value instanceof Db_Expression) {
			return array('chatPublisherId', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".chatPublisherId");
		if (strlen($value) > 31)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".chatPublisherId");
		return array('chatPublisherId', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the chatPublisherId field
	 * @return {integer}
	 */
	function maxSize_chatPublisherId()
	{

		return 31;			
	}

	/**
	 * Returns schema information for chatPublisherId column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_chatPublisherId()
	{

return array (
  0 => 
  array (
    0 => 'varbinary',
    1 => '31',
    2 => '',
    3 => false,
  ),
  1 => false,
  2 => '',
  3 => '',
);			
	}

	/**
	 * Method is called before setting the field and verifies if value is string of length within acceptable limit.
	 * Optionally accept numeric value which is converted to string
	 * @method beforeSet_chatStreamName
	 * @param {string} $value
	 * @return {array} An array of field name and value
	 * @throws {Exception} An exception is thrown if $value is not string or is exceedingly long
	 */
	function beforeSet_chatStreamName($value)
	{
		if (!isset($value)) {
			return array('chatStreamName', $value);
		}
		if ($value instanceof Db_Expression) {
			return array('chatStreamName', $value);
		}
		if (!is_string($value) and !is_numeric($value))
			throw new Exception('Must pass a string to '.$this->getTable().".chatStreamName");
		if (strlen($value) > 255)
			throw new Exception('Exceedingly long value being assigned to '.$this->getTable().".chatStreamName");
		return array('chatStreamName', $value);			
	}

	/**
	 * Returns the maximum string length that can be assigned to the chatStreamName field
	 * @return {integer}
	 */
	function maxSize_chatStreamName()
	{

		return 255;			
	}

	/**
	 * Returns schema information for chatStreamName column
	 * @return {array} [[typeName, displayRange, modifiers, unsigned], isNull, key, default]
	 */
	static function column_chatStreamName()
	{

return array (
  0 => 
  array (
    0 => 'varbinary',
    1 => '255',
    2 => '',
    3 => false,
  ),
  1 => true,
  2 => '',
  3 => NULL,
);			
	}

	/**
	 * Retrieves field names for class table
	 * @method fieldNames
	 * @static
	 * @param {string} [$table_alias=null] If set, the alieas is added to each field
	 * @param {string} [$field_alias_prefix=null] If set, the method returns associative array of ('prefixed field' => 'field') pairs
	 * @return {array} An array of field names
	 */
	static function fieldNames($table_alias = null, $field_alias_prefix = null)
	{
		$field_names = array('publisherId', 'streamName', 'insertedTime', 'sentTime', 'byUserId', 'comment', 'instructions', 'chatPublisherId', 'chatStreamName');
		$result = $field_names;
		if (!empty($table_alias)) {
			$temp = array();
			foreach ($result as $field_name)
				$temp[] = $table_alias . '.' . $field_name;
			$result = $temp;
		} 
		if (!empty($field_alias_prefix)) {
			$temp = array();
			reset($field_names);
			foreach ($result as $field_name) {
				$temp[$field_alias_prefix . current($field_names)] = $field_name;
				next($field_names);
			}
			$result = $temp;
		}
		return $result;			
	}
};