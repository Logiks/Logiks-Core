<?php
/**
 * Tests for RateLimit, PreparedStatementBuilder and file-upload validation.
 *
 * @package    Logiks
 * @subpackage Tests
 * @version    6.0
 */

use PHPUnit\Framework\TestCase;

class ControlsTest extends TestCase {

	protected $tmpDir;

	protected function setUp(): void {
		parent::setUp();
		require_once ROOT . 'api/libs/logiksValidation/RateLimit.php';
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		require_once ROOT . 'api/libs/logiksDB/PreparedStatementBuilder.php';
		$this->tmpDir = sys_get_temp_dir() . '/logiks_ctl_' . uniqid();
		mkdir($this->tmpDir);
	}

	protected function tearDown(): void {
		foreach ((array) glob($this->tmpDir . '/*') as $f) @unlink($f);
		@rmdir($this->tmpDir);
		parent::tearDown();
	}

	// ---- RateLimit ----

	public function testRateLimitBlocksAfterMax() {
		$rl = new RateLimit($this->tmpDir);
		for ($i = 0; $i < 3; $i++) {
			$this->assertTrue($rl->check('k', 3, 60));
		}
		$this->assertFalse($rl->check('k', 3, 60));
		$this->assertSame(0, $rl->remaining());
		$this->assertGreaterThan(0, $rl->retryAfter());
	}

	public function testRateLimitKeysAreIndependentAndResettable() {
		$rl = new RateLimit($this->tmpDir);
		$rl->check('a', 1, 60);
		$this->assertFalse($rl->check('a', 1, 60));
		$this->assertTrue($rl->check('b', 1, 60));
		$rl->reset('a');
		$this->assertTrue($rl->check('a', 1, 60));
	}

	public function testClientIpIgnoresForgedHeaderFromUntrustedPeer() {
		$server = ['REMOTE_ADDR' => '203.0.113.5', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4'];
		$this->assertSame('203.0.113.5', RateLimit::clientIp($server, []));
	}

	public function testClientIpUsesForwardedHeaderFromTrustedProxy() {
		$server = ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '9.9.9.9, 198.51.100.7, 10.0.0.1'];
		$this->assertSame('198.51.100.7', RateLimit::clientIp($server, ['10.0.0.1']));
	}

	public function testRateLimitFailsClosedWhenStorageUnavailable() {
		$blocker = $this->tmpDir . '/notadir';
		file_put_contents($blocker, 'x');
		$rl = new RateLimit($blocker . '/sub');
		$this->assertFalse($rl->check('k', 5, 60));
	}

	public function testRateLimitFailsOpenWhenRequested() {
		$blocker = $this->tmpDir . '/notadir';
		file_put_contents($blocker, 'x');
		$rl = new RateLimit($blocker . '/sub', true);
		$this->assertTrue($rl->check('k', 5, 60));
	}

	// ---- PreparedStatementBuilder ----

	protected function sqliteDriver() {
		if (!class_exists('SQLite3')) $this->markTestSkipped('SQLite3 not available');
		$link = new SQLite3(':memory:');
		$link->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
		return new class($link) {
			private $l;
			public function __construct($l) { $this->l = $l; }
			public function get_link() { return $this->l; }
			public function get_table($t) { return $t; }
			public function isReadonly() { return false; }
		};
	}

	public function testPreparedStatementRoundTrip() {
		$b = new PreparedStatementBuilder($this->sqliteDriver());
		$this->assertSame(1, $b->insert('users', ['name' => 'Ann'])->execute());
		$rows = $b->select('users', ['id', 'name'])->where('name = ?', ['Ann'])->execute();
		$this->assertCount(1, $rows);
		$this->assertSame('Ann', $rows[0]['name']);
		$this->assertSame(1, $b->update('users', ['name' => 'Bob'])->where('id = ?', [$rows[0]['id']])->execute());
		$this->assertSame(1, $b->delete('users')->where('name = ?', ['Bob'])->execute());
	}

	public function testInjectionPayloadIsTreatedAsData() {
		$b = new PreparedStatementBuilder($this->sqliteDriver());
		$b->insert('users', ['name' => 'Ann'])->execute();
		$rows = $b->select('users')->where('name = ?', ["x' OR '1'='1"])->execute();
		$this->assertSame([], $rows);
		$this->assertCount(1, $b->select('users')->execute(), 'table must be intact');
	}

	public function testMaliciousIdentifiersAreRejected() {
		$b = new PreparedStatementBuilder($this->sqliteDriver());
		$this->expectException(InvalidArgumentException::class);
		$b->select('users; DROP TABLE users');
	}

	public function testUnfilteredDeleteIsRefused() {
		$b = new PreparedStatementBuilder($this->sqliteDriver());
		$this->expectException(LogicException::class);
		$b->delete('users')->execute();
	}

	public function testPlaceholderCountMismatchIsRejected() {
		$b = new PreparedStatementBuilder($this->sqliteDriver());
		$this->expectException(InvalidArgumentException::class);
		$b->select('users')->where('id = ? AND name = ?', [1]);
	}

	// ---- Upload validation ----

	protected function fileEntry(string $name, string $content, int $error = UPLOAD_ERR_OK): array {
		$path = $this->tmpDir . '/' . uniqid();
		file_put_contents($path, $content);
		return ['name' => $name, 'tmp_name' => $path, 'error' => $error, 'size' => strlen($content)];
	}

	protected function validator(array $data) {
		return new class($data) extends InputValidator {
			protected function isUploadedFile(string $path): bool { return is_file($path); }
		};
	}

	protected function png(): string {
		return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
	}

	public function testValidPngUploadPasses() {
		$v = $this->validator(['avatar' => $this->fileEntry('me.png', $this->png())]);
		$this->assertTrue($v->validate(['avatar' => 'required|file|mimes:png,jpg|max:2048']));
	}

	public function testDisguisedPhpFileIsRejected() {
		$v = $this->validator(['avatar' => $this->fileEntry('shell.png', '<?php system($_GET["c"]);')]);
		$this->assertFalse($v->validate(['avatar' => 'required|file|mimes:png,jpg']));
	}

	public function testDisallowedExtensionIsRejected() {
		$v = $this->validator(['avatar' => $this->fileEntry('shell.php', $this->png())]);
		$this->assertFalse($v->validate(['avatar' => 'required|file|mimes:png,jpg']));
	}

	public function testOversizeFileIsRejected() {
		$v = $this->validator(['doc' => $this->fileEntry('a.txt', str_repeat('a', 3000))]);
		$this->assertFalse($v->validate(['doc' => 'required|file|mimes:txt|max:2']));
	}

	public function testMissingRequiredFileFails() {
		$v = $this->validator(['avatar' => ['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0]]);
		$this->assertFalse($v->validate(['avatar' => 'required|file']));
	}

	public function testOptionalEmptyFileIsSkipped() {
		$v = $this->validator(['avatar' => ['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0]]);
		$this->assertTrue($v->validate(['avatar' => 'file|mimes:png']));
	}

	public function testNonUploadedPathIsRejected() {
		// Real validator: a plain file on disk is not an HTTP upload
		$v = new InputValidator(['avatar' => $this->fileEntry('me.png', $this->png())]);
		$this->assertFalse($v->validate(['avatar' => 'required|file']));
	}
}
