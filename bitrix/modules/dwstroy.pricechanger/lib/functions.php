<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Dwstroy\Pricechanger\Functions;
/**
 * Class Fabric
 * Provides function object instance by it's name.
 * Has some builtin function such as: upper, lower, concat and limit.
 * Fires event OnTemplateGetFunctionClass. Handler of the event has to return acclass name not an instance.
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Fabric
{
    protected static $functionMap = array();
    /**
     * Instantiates an function object by function name.
     *
     * @param string $functionName Name of the function in the lower case.
     * @param mixed $data Additional data for function instance.
     *
     * @return FunctionBase
     */
    public static function createInstance($functionName, $data = null) //todo rename createInstance
    {
        if ($functionName === "sin")
            return new Sin($data);
        if ($functionName === "def")
            return new Def($data);
        elseif($functionName === "abs")
            return new Abs($data);
        elseif($functionName === "acos")
            return new Acos($data);
        elseif($functionName === "acosh")
            return new Acosh($data);
        elseif($functionName === "asin")
            return new Asin($data);
        elseif($functionName === "asinh")
            return new Asinh($data);
        elseif($functionName === "atan")
            return new Atan($data);
        elseif($functionName === "atanh")
            return new Atanh($data);
        elseif($functionName === "ceil")
            return new Ceil($data);
        elseif($functionName === "cos")
            return new Cos($data);
        elseif($functionName === "cosh")
            return new Cosh($data);
        elseif($functionName === "floor")
            return new Floor($data);
        elseif($functionName === "round")
            return new Round($data);
        elseif($functionName === "sinh")
            return new Sinh($data);
        elseif($functionName === "sqrt")
            return new Sqrt($data);
        elseif($functionName === "srand")
            return new Srand($data);
        elseif($functionName === "tan")
            return new Tan($data);
        elseif($functionName === "tanh")
            return new Tanh($data);
        elseif (isset(self::$functionMap[$functionName]))
        {
            $functionClass = self::$functionMap[$functionName];
            return new $functionClass($data);
        }
        else
        {
            $event = new \Bitrix\Main\Event("dwstroy.pricechanger", "OnTemplateGetFunctionClass", array($functionName));
            $event->send();
            if ($event->getResults())
            {
                foreach($event->getResults() as $evenResult)
                {
                    if($evenResult->getResultType() == \Bitrix\Main\EventResult::SUCCESS)
                    {
                        $functionClass = $evenResult->getParameters();
                        if (is_string($functionClass) && class_exists($functionClass))
                        {
                            self::$functionMap[$functionName] = $functionClass;
                        }
                        break;
                    }
                }
            }
            if (isset(self::$functionMap[$functionName]))
            {
                $functionClass = self::$functionMap[$functionName];
                return new $functionClass($data);
            }
        }
        return new FunctionBase($data);
    }
}

/**
 * Class FunctionBase
 * Base class for all function objects processed by engine.
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class FunctionBase
{
    protected $data = null;

    /**
     * @param mixed|null $data Additional data for function instance.
     */
    function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return "";
    }

    /**
     * Helper function. Concatenates all the parameters into a string.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    protected function parametersToString(array $parameters)
    {
        $result = array();
        foreach ($parameters as $param)
        {
            if (is_array($param))
                $result[] = implode(" ", $param);
            elseif ($param != "")
                $result[] = $param;
        }
        return implode(" ", $result);
    }

    /**
     * Helper function. Concatenates all the parameters into a string.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    protected function parametersToDouble($parameters)
    {
        if( is_array($parameters) )
            return 0;
        if( is_object($parameters) )
            return 0;

        return doubleval($parameters);
    }

    /**
     * Helper function. Gathers all the parameters into an flat array.
     *
     * @param array $parameters Function parameters.
     *
     * @return array
     */
    protected function parametersToArray(array $parameters)
    {
        $result = array();
        foreach ($parameters as $param)
        {
            if (is_array($param))
            {
                foreach ($param as $p)
                    $result[] = $p;
            }
            elseif ($param != "")
            {
                $result[] = $param;
            }
        }
        return $result;
    }
}

/**
 * Class Def
 * Represents def($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Def extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return $this->parametersToDouble($parameters);
    }
}

/**
 * Class Sin
 * Represents sin($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Sin extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return sin($this->parametersToDouble($parameters));
    }
}

/**
 * Class Abs
 * Represents abs($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Abs extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return abs($this->parametersToDouble($parameters));
    }
}

/**
 * Class Acos
 * Represents acos($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Acos extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return acos($this->parametersToDouble($parameters));
    }
}

/**
 * Class Acosh
 * Represents acosh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Acosh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return acosh($this->parametersToDouble($parameters));
    }
}

/**
 * Class Asin
 * Represents asin($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Asin extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return asin($this->parametersToDouble($parameters));
    }
}

/**
 * Class Asinh
 * Represents asinh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Asinh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return asinh($this->parametersToDouble($parameters));
    }
}

/**
 * Class Atan
 * Represents atan($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Atan extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return atan($this->parametersToDouble($parameters));
    }
}

/**
 * Class Atanh
 * Represents atanh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Atanh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return atanh($this->parametersToDouble($parameters));
    }
}

/**
 * Class Ceil
 * Represents ceil($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Ceil extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return ceil($this->parametersToDouble($parameters));
    }
}
/**
 * Class Cos
 * Represents cos($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Cos extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return cos($this->parametersToDouble($parameters));
    }
}
/**
 * Class Cosh
 * Represents cosh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Cosh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return cosh($this->parametersToDouble($parameters));
    }
}
/**
 * Class Floor
 * Represents floor($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Floor extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return floor($this->parametersToDouble($parameters));
    }
}
/**
 * Class Round
 * Represents round($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Round extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return round($this->parametersToDouble($parameters));
    }
}
/**
 * Class Sinh
 * Represents sinh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Sinh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return sinh($this->parametersToDouble($parameters));
    }
}
/**
 * Class Sqrt
 * Represents sqrt($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Sqrt extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return sqrt($this->parametersToDouble($parameters));
    }
}
/**
 * Class Srand
 * Represents srand($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Srand extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return srand($this->parametersToDouble($parameters));
    }
}
/**
 * Class Tan
 * Represents tan($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Tan extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return tan($this->parametersToDouble($parameters));
    }
}
/**
 * Class Tanh
 * Represents tanh($val).
 *
 * @package Dwstroy\Pricechanger\Functions
 */
class Tanh extends FunctionBase
{
    /**
     * Called by engine to process function call.
     *
     * @param array $parameters Function parameters.
     *
     * @return string
     */
    public function calculate($parameters)
    {
        return tanh($this->parametersToDouble($parameters));
    }
}