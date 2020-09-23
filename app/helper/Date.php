<?php

/**
 * Date Helper Class
 * @author Amit
 */

namespace App\Helper;

use \DateTimeZone;
use \DateTime;
use \DateInterval;
use \DatePeriod;

class Date {

    const DATE_FORMAT_SHORT = 'm/d/Y';
    const DATE_FORMAT_MEDIUM = 'd M Y';
    const DATE_FORMAT_LONG = 'l, d F Y';

    /**
     * Format Date from time
     * @param int $time
     * @param string $format
     * @return string
     */
    public static function formatDate($time, $format = 'Y-m-d') {
        if (is_numeric($time)) {
            $time = gmdate('Y-m-d H:i:s', $time);
        }
        $dateTime = new \DateTime($time);
        return $dateTime->format($format);
    }

    /**
     * Get the current date (01 - 31)
     * @return string
     */
    public static function getCurrentDay() {
        return date('d');
    }

    /**
     * Get the current Month (1 -12)
     * @return int
     */
    public static function getCurrentMonth() {
        return date('n');
    }

    /**
     * Get the current year
     * @return int
     */
    public static function getCurrentYear() {
        return date('Y');
    }

    /**
     * Return Current Date Time
     * @param string $format
     * @return string
     */
    public static function currentTimeDate($format = 'Y-m-d H:i:s') {
        $time = gmdate('Y-m-d H:i:s', time());
        $dateTime = new \DateTime($time);
        return $dateTime->format($format);
    }

    /**
     * Format jQuery Datepicker date to MySql datetime format
     * @param string $date
     * @param string $localTimezoneCity
     * @return string
     */
    public static function formatDatepickerToMySql($date = NULL, $localTimezoneCity = null) {
        if ($date) {
            $localToMysql = self::localToSQL($date, $localTimezoneCity);
            return $localToMysql;
        }
    }

    /**
     * Format MySql datetime to Datepicker date format
     * @param string $date
     * @param string $localTimezoneCity
     * @return string
     */
    public static function formatMySqlToDatepicker($date = NULL, $localTimezoneCity = null) {
        if ($date) {
            $sqlToLocal = self::SQLToLocal($date, $localTimezoneCity, FALSE, 'm/d/Y');
            return $sqlToLocal;
        }
    }

    /**
     * Calculating date difference between two dates
     * @param string $start
     * @param string $end
     * @return string
     */
    public static function dateDiff($start, $end, $string="Day(s)") {
        $dateStart = new \DateTime($start);
        $dateEnd = new \DateTime($end);
        $diff = $dateEnd->diff($dateStart)->format("%a ".$string);
        return $diff;
    }

    /**
     * Calculating date Lowest difference between two dates
     * @param string $start
     * @param string $current
     * @return array
     */
    public static function dateDiffLowest($start, $current) {
        $dateStart = new \DateTime($start);
        $currentDate = new \DateTime($current);
        $diff = 0;
        $type ='sec';
        if ($dateStart > $currentDate) {
            $diff = $currentDate->diff($dateStart)->format("%a");
            $type ='day';
            if($diff < 1) {
                $diff = $currentDate->diff($dateStart)->format("%h");
                $type ='hour';
            }
            if($type == 'hour' && $diff < 1) {
                $diff = $currentDate->diff($dateStart)->format("%i");
                $type ='minute';
            }
        }
        $return['diff'] = $diff;
        $return['type'] = $type;
        return $return;
    }

    /**
     * Return all dates between two date
     * @param string $startDate
     * @param string $endDate
     * @param string $format
     * @param bool $string
     * @return array
     */
    public static function getDates($startDate, $endDate, $format = 'Y-m-d', $string = false) {
        $dates = array();
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $datePeriod = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->modify('+1 day'));
        foreach ($datePeriod as $date) {
            $dates[] = $string ? '"' . $date->format($format) . '"' : $date->format($format);
        }
        return $dates;
    }
    
    /**
     * takes local time ("server" time which should be set properly and overridden with the users local settings when applicable)
     * so when user, seller, or admin is logged in make sure to call on every page load after session start date_default_timezone_set($userTimezonePreference)
     * else it will fall back to true server time (we should be defaulting server time to 'America/New_York' and not UTC, db should be defaulted to UTC)
     * 
     * example:
     * <code>
     * // to go from any db to user form (or list)
     * // in user new or edit form
     * ...
     * $event_date_utc = $myModel->event_date;
     * $event_date_local = \App\Helper\Date::SQLToLocal($event_date_utc);
     * $event_date_tz = \App\Helper\Date::localTimezoneAbbrivation($event_date_local);
     * echo "<input name='event_date' value='$event_date_local' /> $event_date_tz";
     * ...
     * 
     * // in create or save controller
     * ...
     * $event_date_local = $this->request->getPost('event_date');
     * $event_date_utc = \App\Helper\Date::localToSQL($event_date_local);
     * $myModel->setEventDate($event_date_utc);
     * ...
     * $myModel->save();
     * ...
     * </code>
     * 
     * @param int|string $localTime Can be either unix timestamp, local strtotime string or null to use current time
     * @param string $localTimezoneCity A properly formated timezone city/region string (ie 'America/New_York', etc...)
     * @param boolean $includeTime true to include full datetime format, false to include only date (useful for ignoring time portion and only keeping date where time is not needed) 
     * @param mix $dayBegin If 'true' then include begining time of a day (00:00:00) else ending time of a day (23:59:59), this will only apply then $indludeTime is 'false'
     */

    public static function localToSQL($localTime = null, $localTimezoneCity = null, $includeTime = true, $dayBegin = null, $dateFromat = 'Y-m-d') {

        
        $fromTZ = new DateTimeZone($localTimezoneCity ?: date_default_timezone_get());
        $toTZ = new DateTimeZone('UTC');
        
        if (is_numeric($localTime)) {
            $dt = new DateTime(null, $fromTZ);  //always force UTC from DB
            $dt->setTimestamp($localTime);
        } else {
            $dt = new DateTime($localTime, $fromTZ);  //always force UTC from DB
        }
        $dt->setTimezone($toTZ);
        

        return $dt->format($dateFromat . ($includeTime ? ' H:i:s' : ($dayBegin !== null ? ($dayBegin ? ' 00:00:00' : ' 23:59:59') : '')));

    }
    
    /**
     * takes UTC time from db and converts it to local time formated time
     * you can then use a formatting function to properly display it in whatever format you want
     * relies on date_default_timezone_get if you don't pass $localTimezoneCity, if not set then uses NY time as local (we should always be setting it so that shouldn't happen)
     * 
     * @param string|int $sqlTime A sql datetime or date string in UTC time, also allows for unix timestamp so easily convert timestamp to local time (unix timestamp is always UTC)
     * @param string $localTimezoneCity A properly formated timezone city/region string (ie 'America/New_York', etc...)
     * @param string $includeTZAbbriviation Adds ' T' onto format so you can easily include or exclude it, generally only useful if you are not overriding $dateFormat
     * @param string|boolean $dateFormat A format string to be used for DateTime::format(), if set to false will return unix timestamp instead
     */
    public static function SQLToLocal($sqlTime = null, $localTimezoneCity = null, $includeTZAbbriviation = true, $dateFormat = 'Y-m-d H:i:s') {
        
        $fromTZ = new DateTimeZone('UTC'); 
        $toTZ = new DateTimeZone($localTimezoneCity ?: date_default_timezone_get());
        
        if (is_numeric($sqlTime)) {
            $dt = new DateTime(null, $fromTZ);  //always force UTC from DB
            $dt->setTimestamp($sqlTime);
        } else {
            $dt = new DateTime($sqlTime, $fromTZ);  //always force UTC from DB
        }
        $dt->setTimezone($toTZ);
        
        if ($dateFormat === false) return $dt->getTimestamp();
        else return $dt->format(($dateFormat ?: 'Y-m-d H:i:s') . ($includeTZAbbriviation ? ' T' : ''));
    }
    
    /**
     * really just an alias for php's date('T') function but provided for a consistent set of tools to allow you to allow a complete workflow for db/local datetime handling
     * Outputs the current timezone abbreviation or what the abbreviation will be if you supply the time as an argument 
     * ie just because we are now in EST does not mean that in 6 months from now it will be EST (it actually will be EDT since Standard time is < 6 months long)
     * 
     * To be used in conjunction with self::SQLToLocal()
     * <code>
     * $last_updated_db = $myModel->update_time;
     * $last_updated_local = \App\Helper\Date::SQLToLocal($last_updated_db);
     * $tz = \App\Helper\Date::localTimezoneAbbrivation($last_updated_local);
     * 
     * echo "Record last modified on $last_updated_local $tz";
     * </code>
     * 
     * really you could just provide the format in self::SQLToLocal() but then you always have to be thinking about which code is the one you want
     * also if the time would be going into a control to set time then the timezone would probably be separate (ie echo "<input value='$last_updated_local'> $tz";
     * 
     * @param int|string $localTime The current local time or unix timestamp
     * @return string
     */
    public static function localTimezoneAbbrivation($localTime = null, $localTimezoneCity = null) {
        
        $fromTZ = new DateTimeZone($localTimezoneCity ?: date_default_timezone_get());
        
        if (is_numeric($localTime)) {
            $dt = new DateTime(null, $fromTZ);  //always force UTC from DB
            $dt->setTimestamp($localTime);
        } else {
            $dt = new DateTime($localTime, $fromTZ);  //always force UTC from DB
        }
        
        return $dt->format('T');
    }
    
    /**
     * really just an alias for php's date('e') function but provided for a consistent set of tools to allow you to allow a complete workflow for db/local datetime handling
     * Outputs the current timezone name or what the name will be if you supply the time as an argument
     * unlike self::localTimezoneAbbrivation() the timezone name should always be the same regardless of the actual date
     * for consistency with self::localTimezoneAbbrivation() we let you pass time just in case but it really shouldn't make a difference
     * 
     * likely needed for displaying to the user the current timezone region (like in header or footer "your current timezone region is <?php echo \App\Helper\Date::localTimezoneName(); ?>")
     * 
     * @param int|string $localTime The current local time or unix timestamp
     * @return string
     */
    public static function localTimezoneName($localTime = null, $localTimezoneCity = null) {
        
        // just returns the processed name, really we should use the calculated name from DateTime::format('e') but that does not return the name, it only returns the abbriviation which is wrong
        // for now just return the string that we will use for our calcs in the other functions
        // this *could* be wrong if we want to set the tz based on offset or abbriviation in the future (ie $localTimezoneCity = '-5:00' or $localTimezoneCity = 'EST'), for now we are not and proabaly should never support that
        return $localTimezoneCity ?: date_default_timezone_get();
        
        $fromTZ = new DateTimeZone($localTimezoneCity ?: date_default_timezone_get());
        
        if (is_numeric($localTime)) {
            $dt = new DateTime(null, $fromTZ);  //always force UTC from DB
            $dt->setTimestamp($localTime);
        } else {
            $dt = new DateTime($localTime, $fromTZ);  //always force UTC from DB
        }
        
        return $dt->format('e');  // does not work, will always return Abbriviation
    }
    
    /**
     * Returns a list of valid timezone regions that would be valid for use with above timezone functions
     * generally the timezone string should be coming from the db timezone.code table and this list is just to pre-populate that table
     * 
     * to re-populate the timezones table to the db you can use:
     * <code>    
     * $allTimezones = \App\Helper\Date::listTimezoneNames();
     * $allTimezones = array_combine($allTimezones, $allTimezones);
     * $countries = \App\Model\Country::find(['order' => 'name ASC']);
     * $tzCnt = 0;
     * foreach ($countries as $country) {
     *     $timezones = \App\Helper\Date::listTimezoneNames($country->iso_code_2);
     *     
     *     if ($timezones) {
     *         foreach ($timezones as $tz) {
     *             $tzCnt++;
     *             @list($tzContinent, $tzStateCity) = explode('/', $tz, 2);
     *             $displayName = preg_replace('#[^a-z0-9 -]+#i', ' ', str_replace('/', ' - ', $tzStateCity)) ?: $tzContinent;
     *             $tmpArr = ['country_id' => $country->country_id, 'region' => $tzContinent, 'name' => $displayName, 'code' => $tz];
     *             $tmpArr['sql'] = "({$tmpArr['country_id']}, '{$tmpArr['region']}', '{$tmpArr['name']}', '{$tmpArr['code']}')";
     *             $tz_data[$country->country_id][] = $tmpArr;
     *             unset($allTimezones[$tz]);
     *         }

     *         echo "INSERT INTO timezone (country_id, region, name, code) VALUES " . implode(', ', array_column($tz_data[$country->country_id], 'sql')) . ";<br>\r\n";
     *     } else {
     *         echo "#<font color=red>no timezones found for country " . implode(', ', $country->toArray()) . "</font><br>\r\n";
     *     }
     * }
     * if ($allTimezones) {
     *     foreach ($allTimezones as $tz) {
     *         $tzCnt++;
     *         @list($tzContinent, $tzStateCity) = explode('/', $tz, 2);
     *         $displayName = preg_replace('#[^a-z0-9 -]+#i', ' ', str_replace('/', ' - ', $tzStateCity)) ?: $tzContinent;
     *         $tmpArr = ['region' => $tzContinent, 'name' => $displayName, 'code' => $tz];
     *         $tmpArr['sql'] = "('{$tmpArr['region']}', '{$tmpArr['name']}', '{$tmpArr['code']}')";
     *         $tz_data[0][] = $tmpArr;
     *         unset($allTimezones[$tz]);
     *     }
     *     echo "INSERT INTO timezone (region, name, code) VALUES " . implode(', ', array_column($tz_data[0], 'sql')) . ";<br>\r\n";
     * }
     * </code>
     * 
     * @return array
     */
    public static function listTimezoneNames($country = null, $which = DateTimeZone::ALL)
    {
        return DateTimeZone::listIdentifiers($country !== null ? DateTimeZone::PER_COUNTRY : ($which ?: DateTimeZone::ALL), $country);
    }

    /*
    *Return a list of all dates between startdate and  enddate
    * @return array of dates
    */
    public static function getDateRange($startDate = NULL,$endDate = NULL/*, $timeZone = null*/)
    {
//        $toTZ = new DateTimeZone($timeZone ?: date_default_timezone_get());
//        $begin = new DateTime( $startDate, $toTZ );
//        $end = new DateTime( $endDate, $toTZ );
        
        $begin = new DateTime( $startDate );
        $end = new DateTime( $endDate );
        $end->modify('+1 day');
        $interval = DateInterval::createFromDateString('1 day');
        return new DatePeriod($begin, $interval, $end);
    }
    
    /**
     * Get the TimeZone name based on time offset
     * @param int $offset
     * @return string
     */
    public static function getTimezoneNameByOffset($offset = -1) {
        
        $timeZoneOffset = $offset > 0 ? ($offset * 60) : $offset;
        
        $timezoneName = timezone_name_from_abbr("", $timeZoneOffset, false);
        
        return $timezoneName;
    }

    /**
     * Get the TimeZone offset based on the TimeZone name
     * @param string $timezoneName
     * @return int
     */
    public static function getTimezoneOffsetByName ($timezoneName = null) {
        $defaultTz = $timezoneName ?: date_default_timezone_get();

        $defaultTzObjc = new \DateTimeZone($defaultTz);
        $toTzObjc = new \DateTimeZone("UTC");

        $dt = new \DateTime(null, $toTzObjc);

        $timezoneOffset = $defaultTzObjc->getOffset($dt);
        $timezoneOffset = ($timezoneOffset / 60);
        
        return $timezoneOffset;
    }

    /**
     * Gets date with interval
     */
    public static function getDateFromInterval($interval = '+1 day', $localTime = null, $timeZone = 'UTC', $format = 'Y-m-d H:i:s'){
        $today = self::localToSQL($localTime,$timeZone);
        $dateTime = new \DateTime($today, new \DateTimeZone($timeZone));
        $dateTime->modify($interval);
        return $dateTime->format($format);
    }

    /**
     * Get starting/ending datetime
     * @param bool $dayBegin If 'true' then include begining time of a day (00:00:00) else ending time of a day (23:59:59)
     * @param string|int $sqlTime A sql datetime or date string in UTC time
     * @param string $timeZone timezone city/region format
     * @param bool $convertUTC If 'true' then return timezone in UTC format else return current timezone datetime
     */
    public static function getLocalDayBeginOrEndUTC($dayBegin = true, $sqlTime = null, $timeZone = null, $convertUTC = true, $format = 'Y-m-d'){
        $date = self::SQLToLocal($sqlTime,$timeZone,false,$format);
        $tz = new DateTimeZone($timeZone ?: date_default_timezone_get());
        $dateTime = new \DateTime($date,$tz);
        $date = $dateTime->format($format.($dayBegin?" 00:00:00" : ' 23:59:59'));
        return $convertUTC ? self::localToSQL($date,$timeZone) :$date;
    }

}
