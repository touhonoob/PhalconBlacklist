<?php

namespace PhalconBlacklist\Models;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date Mar 7, 2015
 */
class CIDR extends \Phalcon\Mvc\Model
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $first;

    /**
     * @var int
     */
    public $last;

    /**
     * @var string
     */
    public $label;

    public function getSource()
    {
        return 'blacklist_cidr';
    }

    /**
     * Check if $ip is on the CIDR blacklist
     * @param string $ip
     * @return boolean
     */
    public static function checkIP($ip)
    {
        $ip = ip2long($ip);

        return static::count([
                    "first <= ?0 AND last >= ?1",
                    "bind" => [$ip, $ip]
                ]) > 0;
    }

    /**
     * Create record from CIDR address
     * @param string $cidr
     * @return CIDR created cidr record
     * @throws \InvalidArgumentException
     */
    public static function createFromCIDR($cidr, $label = null)
    {
        $parts = static::validateCIDR($cidr);
        $CIDR = new CIDR();
        $CIDR->first = $parts['ip'];
        $CIDR->last = static::calculateLast($parts['ip'], $parts['broadcast']);
        $CIDR->label = $label;
        return $CIDR;
    }

    /**
     * Create record from single IP
     * @param string $ip
     * @param string $label
     * @return CIDR
     * @throws \InvalidArgumentException
     */
    public static function createFromIP($ip, $label = null)
    {
        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
            throw new \InvalidArgumentException("Not valid IP");
        }

        return self::createFromCIDR("$ip/32", $label);
    }

    /**
     * Calculate last IP from first IP & netmask
     * Ref: http://www.gerbenjacobs.nl/get-ip-range-by-cidr-notation/
     * @param int $first
     * @param int $netmask
     * @return int
     */
    public static function calculateLast($first, $netmask)
    {
        $first_bin = \str_pad(\decbin($first), 32, "0", \STR_PAD_LEFT);
        $netmask_bin = \str_pad(\str_repeat("1", (int) $netmask), 32, "0", \STR_PAD_RIGHT);
        $last_bin = '';

        for ($i = 0; $i < 32; $i++) {
            if ($netmask_bin[$i] === "1") {
                $last_bin .= $first_bin[$i];
            } else {
                $last_bin .= "1";
            }
        }

        return \bindec($last_bin);
    }

    /**
     * Vadliate and return cidr parts
     * @param string $cidr
     * @return array $parts
     * @throws \InvalidArgumentException
     */
    public static function validateCIDR($cidr)
    {
        $parts = explode("/", $cidr);
        if (sizeof($parts) !== 2) {
            throw new \InvalidArgumentException("Not valid CIDR: $cidr");
        }

        $ip = $parts[0];
        $broadcast = intval($parts[1]);

        if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
            throw new \InvalidArgumentException("Not valid ip: $cidr");
        }

        if ($broadcast < 8 || $broadcast > 32) {
            throw new \InvalidArgumentException("Broadcast should be between 8 and 32: $cidr");
        }

        return [
            'ip' => ip2long($ip),
            'broadcast' => $broadcast
        ];
    }
}
