<?php

namespace Cadesign\Natali;

class Assoc
{
    private const _TABLE = 'cadesign_natali_assoc';

    public static function getWpID($nataliCode)
    {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . self::_TABLE . " WHERE NATALI_ID='" . esc_sql($nataliCode) . "' LIMIT 1";

        $result = $wpdb->get_row($wpdb->prepare($sql));
        return $result->WP_ID;
    }

    public static function save($wpId, $nataliCode)
    {
        global $wpdb;
        if (!self::getWpID($nataliCode))
        {
            $wpdb->insert(
                $wpdb->prefix . self::_TABLE,
                [
                    'WP_ID' => (int)$wpId,
                    'NATALI_ID' => esc_sql($nataliCode)
                ]
            );
        }
    }

    public static function getFirst()
    {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . self::_TABLE . " WHERE NATALI_ID NOT LIKE '%-%-%' LIMIT 1";

        return $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
    }

    public static function deleteAssoc($nataliID)
    {
        global $wpdb;
        $sql = "DELETE FROM " . $wpdb->prefix . self::_TABLE . " WHERE NATALI_ID LIKE '" . esc_sql($nataliID) . "%'";
        $wpdb->query($wpdb->prepare($sql));

        return true;
    }

    public static function getCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) as CNT FROM " . $wpdb->prefix . self::_TABLE . " WHERE NATALI_ID NOT LIKE '%-%-%' LIMIT 1";
        $result = $wpdb->get_row($wpdb->prepare($sql));

        return $result->CNT;
    }

	public static function truncate()
	{
		global $wpdb;

		$sql = "TRUNCATE TABLE ". $wpdb->prefix . self::_TABLE ;

		$wpdb->query($wpdb->prepare($sql));
    }

    public static function getNextForDelete()
	{
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . self::_TABLE . " WHERE NATALI_ID NOT LIKE '%-%-%'  LIMIT " . (int)$_REQUEST['offset'] . ", 1";

		return $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
	}
}