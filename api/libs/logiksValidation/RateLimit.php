<?php
/**
 * Rate Limiter
 *
 * Fixed-window request counter backed by files, so it works per client across
 * PHP processes without needing a session, database or cache server.
 *
 * Example usage:
 * ```php
 * $limit = new RateLimit();
 * if (!$limit->check('login:' . RateLimit::clientIp(), 5, 60)) {
 *     http_response_code(429);
 *     header('Retry-After: ' . $limit->retryAfter());
 *     exit('Too many requests');
 * }
 * ```
 *
 * @package    Logiks
 * @subpackage Validation
 * @version    6.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

class RateLimit {

	/**
	 * @var string Directory where counters are stored
	 */
	protected $dir;

	/**
	 * @var bool Whether check() allows the request when storage is unavailable
	 */
	protected $failOpen = false;

	/**
	 * @var int Seconds until the current window resets (after the last check)
	 */
	protected $retryAfter = 0;

	/**
	 * @var int Requests left in the current window (after the last check)
	 */
	protected $remaining = 0;

	/**
	 * Constructor
	 *
	 * @param string|null $dir Storage directory (defaults to tmp/ratelimit/)
	 * @param bool $failOpen Allow requests when the counter store is unavailable.
	 *                       Default false (fail closed), which suits login throttling;
	 *                       use true for broad limits where an outage must not block everyone.
	 */
	public function __construct(?string $dir = null, bool $failOpen = false) {
		$this->failOpen = $failOpen;
		if ($dir === null) {
			$dir = ROOT . 'tmp/ratelimit/';
		}
		$this->dir = rtrim($dir, '/\\') . '/';
	}

	/**
	 * Record one attempt for $key and report whether it is within the limit.
	 *
	 * @param string $key     Identifier to limit, e.g. "login:" . client IP
	 * @param int    $max     Maximum attempts allowed per window
	 * @param int    $seconds Window length in seconds
	 * @return bool True if the attempt is allowed, false if the limit is exceeded
	 */
	public function check(string $key, int $max, int $seconds): bool {
		$max = max(1, $max);
		$seconds = max(1, $seconds);

		if (!$this->ensureDir()) {
			// Without storage we cannot enforce the limit
			$this->remaining = $this->failOpen ? $max : 0;
			$this->retryAfter = $this->failOpen ? 0 : $seconds;
			return $this->failOpen;
		}

		$now = time();
		$file = $this->fileFor($key);
		$fh = @fopen($file, 'c+');
		if ($fh === false || !flock($fh, LOCK_EX)) {
			if ($fh) fclose($fh);
			$this->remaining = $this->failOpen ? $max : 0;
			$this->retryAfter = $this->failOpen ? 0 : $seconds;
			return $this->failOpen;
		}

		$state = json_decode((string) stream_get_contents($fh), true);
		if (!is_array($state) || !isset($state['start'], $state['count']) || ($now - (int) $state['start']) >= $seconds) {
			$state = ['start' => $now, 'count' => 0, 'ttl' => $seconds];
		}

		$state['count']++;
		$state['ttl'] = $seconds;

		ftruncate($fh, 0);
		rewind($fh);
		fwrite($fh, json_encode($state));
		fflush($fh);
		flock($fh, LOCK_UN);
		fclose($fh);

		$this->retryAfter = max(0, ((int) $state['start'] + $seconds) - $now);
		$this->remaining = max(0, $max - (int) $state['count']);

		if (mt_rand(1, 100) === 1) {
			$this->cleanup();
		}

		return $state['count'] <= $max;
	}

	/**
	 * Seconds until the window resets, as of the last check().
	 */
	public function retryAfter(): int {
		return $this->retryAfter;
	}

	/**
	 * Attempts remaining in the window, as of the last check().
	 */
	public function remaining(): int {
		return $this->remaining;
	}

	/**
	 * Forget all attempts for $key (e.g. after a successful login).
	 */
	public function reset(string $key): void {
		$file = $this->fileFor($key);
		if (is_file($file)) {
			@unlink($file);
		}
	}

	/**
	 * Determine the client IP address.
	 *
	 * X-Forwarded-For is only honoured when the direct peer (REMOTE_ADDR) is
	 * listed in RATELIMIT_TRUSTED_PROXIES (comma separated exact IPs), because
	 * otherwise any client can forge the header to evade the limit.
	 *
	 * @param array|null $server Server variables (defaults to $_SERVER)
	 * @param array|null $trustedProxies Trusted proxy IPs (defaults to config)
	 */
	public static function clientIp(?array $server = null, ?array $trustedProxies = null): string {
		$server = $server ?? $_SERVER;
		$remote = (string) ($server['REMOTE_ADDR'] ?? '');

		if ($trustedProxies === null) {
			$cfg = function_exists('getConfig') ? (string) getConfig('RATELIMIT_TRUSTED_PROXIES') : '';
			$trustedProxies = array_filter(array_map('trim', explode(',', $cfg)));
		}

		if ($remote !== '' && in_array($remote, $trustedProxies, true) && !empty($server['HTTP_X_FORWARDED_FOR'])) {
			// Walk right to left, skipping our own proxies; first other address is the client
			$hops = array_reverse(array_map('trim', explode(',', $server['HTTP_X_FORWARDED_FOR'])));
			foreach ($hops as $hop) {
				if (filter_var($hop, FILTER_VALIDATE_IP) && !in_array($hop, $trustedProxies, true)) {
					return $hop;
				}
			}
		}

		return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : 'unknown';
	}

	protected function fileFor(string $key): string {
		return $this->dir . hash('sha256', $key) . '.json';
	}

	protected function ensureDir(): bool {
		if (is_dir($this->dir)) {
			return is_writable($this->dir);
		}
		return @mkdir($this->dir, 0750, true) || is_dir($this->dir);
	}

	/**
	 * Remove counters whose window ended long ago.
	 */
	protected function cleanup(): void {
		$now = time();
		foreach ((array) glob($this->dir . '*.json') as $file) {
			$state = json_decode((string) @file_get_contents($file), true);
			$ttl = is_array($state) ? (int) ($state['ttl'] ?? 0) : 0;
			$start = is_array($state) ? (int) ($state['start'] ?? 0) : 0;
			if ($now - $start > max($ttl, 1) * 2) {
				@unlink($file);
			}
		}
	}
}
