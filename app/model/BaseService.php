<?php

/**
 * @author Hána František
 */
abstract class BaseService
{

    /**
     * reference na třídu typu Table
     *
     * @var instance of BaseTable
     */
    protected $table;

    /**
     * slouží pro komunikaci se skautISem
     *
     * @var Skautis\Skautis
     */
    protected $skautis;

    /**
     * používat lokální úložiště?
     *
     * @var bool
     */
    private $useCache = true;

    /**
     * krátkodobé lokální úložiště pro ukládání odpovědí ze skautISU
     *
     * @var type
     */
    private static $storage;

    public function __construct(Skautis\Skautis $skautIS = null)
    {
        $this->skautis = $skautIS;
        self::$storage = [];
    }

    public function getInfo()
    {
        return [
            "ID_Login" => $this->skautis->getUser()->getLoginId(),
            "ID_Role" => $this->skautis->getUser()->getRoleId(),
            "ID_Unit" => $this->skautis->getUser()->getUnitId(),
        ];
    }

    /**
     * ukládá $val do lokálního úložiště
     *
     * @param  mixed $id
     * @param  mixed $val
     * @return mixed
     */
    protected function save($id, $val)
    {
        if ($this->useCache) {
            self::$storage[$id] = $val;
        }
        return $val;
    }

    /**
     * vrací objekt z lokálního úložiště
     *
     * @param  string|int $id
     * @return mixed | FALSE
     */
    protected function load($id)
    {
        if ($this->useCache && array_key_exists($id, self::$storage)) {
            return self::$storage[$id];
        }
        return false;
    }

}
