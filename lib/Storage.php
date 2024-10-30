<?php

namespace CADesign\Natali;

class Storage
{
    private static $main = null; // Объект хранилища
    private $sessionId = ''; // Id хранилища
    private $storage = []; // Хранилище

    /**
     * Storage constructor.
     */
    private function __construct()
    {
        $this->storage = new \WC_Session_Handler();
        $this->storage->init();
    }

    private function __clone()
    {
    }

    /**
     * @return Storage|null
     */
    public static function main()
    {
        if (is_null(self::$main))
        {
            self::$main = new self();
        }

        return self::$main;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->storage;
    }

    /**
     * @param string $id
     * @return array
     */
    public function getByID($id)
    {
        return $this->storage->get($id)??false;
    }

    /**
     * @param string $id
     * @param array $data
     */
    public function set($id, $data)
    {
        $this->storage->set($id, $data);
        $this->storage->save_data();
    }

    /**
     * @param int $id
     * @param array $data
     */
    public function append($id, array $data)
    {
        if (empty($this->storage[$id]))
        {
            $this->storage[$id] = [];
        }
        $this->storage[$id] = array_merge($this->storage[$id], $this->transform($data));
        $this->update();
    }

    public function delete()
    {
        $this->storage = [];
        $this->update();
    }

    /**
     * @param int $id
     */
    public function deleteById($id)
    {
        if (!empty($this->storage[$id]))
        {
            $this->storage[$id] = [];
            $this->update();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function transform(array $data)
    {
        $arResult = [];
        foreach ($data as $key => $value)
        {
            $arResult[strtolower($key)] = $value;
        }

        return $arResult;
    }

    private function update()
    {
        if (!empty($this->storage))
        {
            \set_transient($this->sessionId, $this->storage, 3600);
        }
        else
        {
            \delete_transient($this->sessionId);
        }

        \WC_Cache_Helper::get_transient_version('shipping', true);
    }
}