<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine\Capability;

use function get_object_vars;

/**
 * @author kko
 */
trait ToArrayConvertible
{
    public function toArray() : array
    {
        return $this->toArrayDeep($this);
    }

    private function toArrayDeep($object)
    {
        $out_arr = [];

        if (!empty($object)) {

            $arrObj = is_object($object) ? get_object_vars($object) : $object;

            foreach ($arrObj as $key => $val) {
                if ('ignore' === $key) {
                    continue;
                }

                if (is_string($key) && !is_numeric($key)) {
                    $key = $this->camelCaseToSeparatorCase($key, '_');
                }

                if (is_array($val) || is_object($val)) {
                    $out_arr[$key] = $this->toArrayDeep($val);
                } elseif (null !== $val) {
                    $out_arr[$key] = $val;
                }
            }
        }

        unset($out_arr['ignore']);

        return $out_arr;
    }

    private function camelCaseToSeparatorCase(string $str, string $separator) : string
    {
        $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
        preg_match_all($pattern, $str, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ?
                strtolower($match) :
                lcfirst($match);
        }

        return implode('_', $ret);
    }
}
