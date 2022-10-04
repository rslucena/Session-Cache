<?php

namespace app\Session;

use Exception;

class SessionHandler
{

    protected string $key;
    protected int $time = 10;
    protected string $values;
    private string $action;

    /**
     * Create a temporary session instance
     *
     * @param string $key
     *
     * @return static
     */
    static public function key(string $key): static
    {
        $Instance = new static();
        $Instance->key = $key;
        return $Instance;
    }

    /**
     * Defines the action event
     *
     * @param string $action
     *
     * @return $this
     */
    public function action(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Sets the valid time for the session
     *
     * @param int $minutes
     *
     * @return $this
     */
    public function time(int $minutes = 0): static
    {
        $this->time = $minutes;
        return $this;
    }

    /**
     * Load the temporary content of an instance or session
     *
     * @return array
     */
    public function load(): array
    {

        $hash = hash_hmac('sha256', $this->key . $this->action , 'SESSIONMODE');

        $path = __DIR__ . DIRECTORY_SEPARATOR . $hash . '.session';

        if (file_exists($path) === false) {
            return [];
        }

        $Session = file_get_contents($path);
        $Session = json_decode($Session, true) ?? [];

        $limit = $Session['limit_time'] ?? 0;

        if ($limit < time() === true) {
            return [];
        }

        return $Session['session'] ?? [];
    }

    /**
     * Define a session file
     *
     * @param array $session
     *
     * @return static
     */
    public function set(mixed $session): static
    {
        $limit_time = $this->time !== 0 ? strtotime("+$this->time minutes", time()) : 0;

        $Set = json_encode([
            'session' => $session,
            'limit_time' => $limit_time
        ], JSON_UNESCAPED_UNICODE);

        try {

            $hash = hash_hmac('sha256', $this->key . $this->action , 'SESSIONMODE');

            $path = __DIR__ . DIRECTORY_SEPARATOR . $hash . '.session';

            file_put_contents($path, $Set);

        } catch (Exception) {
        }

        return $this;
    }

    /**
     * Clear session key
     */
    public function wipe(bool $allClear = false): void
    {

        if ($allClear) {
            $files = glob(__DIR__ . '*.session');
            foreach ($files ?? [] as $file) {
                unlink($file);
            }
            return;
        }

        $hash = hash_hmac('sha256', $this->key . $this->action , 'SESSIONMODE');

        if( file_exists( __DIR__ . DIRECTORY_SEPARATOR . $hash . '.session' ) === true ){
            unlink(__DIR__ . DIRECTORY_SEPARATOR . $hash . '.session' );
        }

    }

}