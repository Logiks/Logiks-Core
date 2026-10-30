<?php
/**
 * Prepared Statement Builder
 *
 * Builds parameterized queries on top of a Logiks database driver. Values are
 * always sent as bound parameters; table and column names are validated against
 * a strict identifier pattern, so neither can carry injected SQL.
 *
 * Supports the mysqli and sqlite3 drivers (anything whose get_link() returns a
 * mysqli or SQLite3 object).
 *
 * Example usage:
 * ```php
 * $stmt = new PreparedStatementBuilder($db);
 * $rows = $stmt->select('users', ['id', 'name'])
 *     ->where('id = ?', [$id])
 *     ->orderBy('name', 'ASC')
 *     ->limit(10)
 *     ->execute();
 *
 * $stmt->insert('users', ['name' => $name, 'email' => $email])->execute();
 * $stmt->update('users', ['name' => $name])->where('id = ?', [$id])->execute();
 * $stmt->delete('users')->where('id = ?', [$id])->execute();
 * ```
 *
 * Note: the text of a where() clause is SQL and must be a literal written by
 * the developer, never built from user input. Only the `?` values are bound.
 *
 * @package    Logiks
 * @subpackage Database
 * @version    6.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

class PreparedStatementBuilder {

	/**
	 * @var object Logiks database driver
	 */
	protected $db;

	/**
	 * @var object mysqli or SQLite3 connection
	 */
	protected $link;

	protected $type = null;
	protected $table = '';
	protected $columns = ['*'];
	protected $data = [];
	protected $wheres = [];
	protected $params = [];
	protected $order = [];
	protected $limit = null;
	protected $offset = null;

	/**
	 * @param object $db Logiks database driver (must provide get_link())
	 * @throws InvalidArgumentException if the driver has no usable connection
	 */
	public function __construct($db) {
		if (!is_object($db) || !method_exists($db, 'get_link')) {
			throw new InvalidArgumentException('PreparedStatementBuilder requires a Logiks database driver');
		}
		$link = $db->get_link();
		if (!($link instanceof mysqli) && !($link instanceof SQLite3)) {
			throw new InvalidArgumentException('Unsupported database connection; mysqli or SQLite3 required');
		}
		$this->db = $db;
		$this->link = $link;
	}

	/**
	 * Start a SELECT.
	 *
	 * @param string $table
	 * @param array|string $columns Column names, or '*'
	 */
	public function select(string $table, $columns = '*'): self {
		$this->reset();
		$this->type = 'select';
		$this->table = $this->tableName($table);
		$columns = is_array($columns) ? $columns : [$columns];
		$this->columns = array_map(function ($c) {
			return $c === '*' ? '*' : $this->identifier($c);
		}, $columns);
		return $this;
	}

	/**
	 * Start an INSERT of one row.
	 *
	 * @param array $data column => value
	 */
	public function insert(string $table, array $data): self {
		$this->reset();
		if (empty($data)) throw new InvalidArgumentException('insert() requires at least one column');
		$this->type = 'insert';
		$this->table = $this->tableName($table);
		$this->data = $this->checkedData($data);
		return $this;
	}

	/**
	 * Start an UPDATE. A where() clause is required.
	 *
	 * @param array $data column => value
	 */
	public function update(string $table, array $data): self {
		$this->reset();
		if (empty($data)) throw new InvalidArgumentException('update() requires at least one column');
		$this->type = 'update';
		$this->table = $this->tableName($table);
		$this->data = $this->checkedData($data);
		return $this;
	}

	/**
	 * Start a DELETE. A where() clause is required.
	 */
	public function delete(string $table): self {
		$this->reset();
		$this->type = 'delete';
		$this->table = $this->tableName($table);
		return $this;
	}

	/**
	 * Add a condition (combined with AND). Use ? placeholders for every value.
	 *
	 * @param string $clause e.g. "id = ?" or "status IN (?, ?)"
	 * @param array $params  values for the placeholders, in order
	 * @throws InvalidArgumentException if placeholder and value counts differ
	 */
	public function where(string $clause, array $params = []): self {
		if (substr_count($clause, '?') !== count($params)) {
			throw new InvalidArgumentException('Number of ? placeholders does not match number of parameters');
		}
		$this->wheres[] = '(' . $clause . ')';
		foreach ($params as $p) {
			$this->params[] = $p;
		}
		return $this;
	}

	public function orderBy(string $column, string $direction = 'ASC'): self {
		$direction = strtoupper($direction);
		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new InvalidArgumentException('Order direction must be ASC or DESC');
		}
		$this->order[] = $this->identifier($column) . ' ' . $direction;
		return $this;
	}

	public function limit(int $limit, int $offset = 0): self {
		if ($limit < 0 || $offset < 0) throw new InvalidArgumentException('limit/offset must not be negative');
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	/**
	 * The SQL that will be prepared (placeholders, no values).
	 */
	public function toSql(): string {
		$where = empty($this->wheres) ? '' : ' WHERE ' . implode(' AND ', $this->wheres);

		switch ($this->type) {
			case 'select':
				$sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table . $where;
				if (!empty($this->order)) $sql .= ' ORDER BY ' . implode(', ', $this->order);
				if ($this->limit !== null) $sql .= ' LIMIT ' . (int) $this->limit . ' OFFSET ' . (int) $this->offset;
				return $sql;
			case 'insert':
				$cols = array_keys($this->data);
				return 'INSERT INTO ' . $this->table . ' (' . implode(', ', $cols) . ') VALUES ('
					. implode(', ', array_fill(0, count($cols), '?')) . ')';
			case 'update':
				$sets = array_map(function ($c) { return $c . ' = ?'; }, array_keys($this->data));
				return 'UPDATE ' . $this->table . ' SET ' . implode(', ', $sets) . $where;
			case 'delete':
				return 'DELETE FROM ' . $this->table . $where;
		}
		throw new LogicException('No statement started; call select(), insert(), update() or delete() first');
	}

	/**
	 * Run the statement.
	 *
	 * @return array|int|false  SELECT: array of rows. INSERT/UPDATE/DELETE: affected rows. false on failure.
	 */
	public function execute() {
		$type = $this->type;

		if ($type !== 'select' && method_exists($this->db, 'isReadonly') && $this->db->isReadonly()) {
			return false;
		}
		if (($type === 'update' || $type === 'delete') && empty($this->wheres)) {
			throw new LogicException(strtoupper($type) . ' without where() is refused');
		}

		$sql = $this->toSql();
		$params = ($type === 'insert' || $type === 'update')
			? array_merge(array_values($this->data), $this->params)
			: $this->params;

		$result = ($this->link instanceof mysqli)
			? $this->runMysqli($sql, $params, $type)
			: $this->runSqlite($sql, $params, $type);

		$this->reset();
		return $result;
	}

	/**
	 * ID of the last inserted row.
	 */
	public function lastInsertId() {
		return ($this->link instanceof mysqli) ? $this->link->insert_id : $this->link->lastInsertRowID();
	}

	protected function runMysqli(string $sql, array $params, string $type) {
		$stmt = $this->link->prepare($sql);
		if (!$stmt) return false;

		if (!empty($params)) {
			$types = '';
			foreach ($params as $p) {
				if (is_int($p) || is_bool($p)) $types .= 'i';
				elseif (is_float($p)) $types .= 'd';
				else $types .= 's';
			}
			$values = array_map(function ($p) { return is_bool($p) ? (int) $p : $p; }, array_values($params));
			$stmt->bind_param($types, ...$values);
		}

		if (!$stmt->execute()) {
			$stmt->close();
			return false;
		}

		if ($type === 'select') {
			$res = $stmt->get_result();
			$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
			$stmt->close();
			return $rows;
		}

		$affected = $stmt->affected_rows;
		$stmt->close();
		return $affected;
	}

	protected function runSqlite(string $sql, array $params, string $type) {
		$stmt = $this->link->prepare($sql);
		if (!$stmt) return false;

		foreach (array_values($params) as $i => $p) {
			if (is_int($p) || is_bool($p)) $t = SQLITE3_INTEGER;
			elseif (is_float($p)) $t = SQLITE3_FLOAT;
			elseif ($p === null) $t = SQLITE3_NULL;
			else $t = SQLITE3_TEXT;
			$stmt->bindValue($i + 1, is_bool($p) ? (int) $p : $p, $t);
		}

		$res = $stmt->execute();
		if (!$res) return false;

		if ($type === 'select') {
			$rows = [];
			while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
				$rows[] = $row;
			}
			$res->finalize();
			return $rows;
		}

		$res->finalize();
		return $this->link->changes();
	}

	/**
	 * Validate a column name: letters, digits, underscore, optionally table-qualified.
	 */
	protected function identifier(string $name): string {
		if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $name)) {
			throw new InvalidArgumentException('Invalid identifier: ' . $name);
		}
		return $name;
	}

	/**
	 * Validate a table name and apply the driver's prefix/suffix.
	 */
	protected function tableName(string $table): string {
		$this->identifier($table);
		if (method_exists($this->db, 'get_table')) {
			$table = $this->db->get_table($table);
			$this->identifier($table);
		}
		return $table;
	}

	protected function checkedData(array $data): array {
		$out = [];
		foreach ($data as $col => $value) {
			if (is_array($value) || is_object($value)) {
				throw new InvalidArgumentException('Value for ' . $col . ' must be a scalar or null');
			}
			$out[$this->identifier((string) $col)] = $value;
		}
		return $out;
	}

	protected function reset(): void {
		$this->type = null;
		$this->table = '';
		$this->columns = ['*'];
		$this->data = [];
		$this->wheres = [];
		$this->params = [];
		$this->order = [];
		$this->limit = null;
		$this->offset = null;
	}
}
