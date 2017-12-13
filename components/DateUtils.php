<?php
namespace app\components;

use yii\web\Controller;

class DateUtils
{
	const PERIOD_YEAR = 'YEAR';
	const PERIOD_DAY = 'DAY';
	const PERIOD_HOUR = 'HOUR';
    const PERIOD_MINUTE = 'MINUTE';
    const PERIOD_FROM_DAY = 'FROM_DAY';
    const PERIOD_TO_DAY = 'TO_DAY';

	const FORMAT_MYSQL = 10;
	const FORMAT_TIMESTAMP = 10;

	static $months = array(
   	  	  1 => 'января',
   	  	  2 => 'февраля',
   	  	  3 => 'марта',
   	  	  4 => 'апреля',
   	  	  5 => 'мая',
   	  	  6 => 'июня',
   	  	  7 => 'июля',
   	  	  8 => 'августа',
   	  	  9 => 'сентября',
   	  	  10 => 'октября',
   	  	  11 => 'ноября',
   	  	  12 => 'декабря',
	);

	static $months_wo_days = array(
   	  	  1 => 'январь',
   	  	  2 => 'февраль',
   	  	  3 => 'март',
   	  	  4 => 'апрель',
   	  	  5 => 'май',
   	  	  6 => 'июнь',
   	  	  7 => 'июль',
   	  	  8 => 'август',
   	  	  9 => 'сентябрь',
   	  	  10 => 'октябрь',
   	  	  11 => 'ноябрь',
   	  	  12 => 'декабрь',
	);

	static $period_format = array(
		self::PERIOD_YEAR => 'yyyy-MM-dd',
		self::PERIOD_DAY => 'yyyy-MM-dd',
		self::PERIOD_HOUR => 'yyyy-MM-dd HH:mm:ss',
        self::PERIOD_MINUTE => 'yyyy-MM-dd HH:mm:00', //округление до минут
        self::PERIOD_FROM_DAY => 'yyyy-MM-dd 00:00:00', //с  начала дня
        self::PERIOD_TO_DAY => 'yyyy-MM-dd 23:59:59', // до конца дня

	);

	/**
	 * на входе получаем дату в виде стринга (который может обработать DateTime), на выходе имеем unixTime
	 * @param $dateTime int|string
     * @param $gtm bool
	 * @return int
	 */
	public static function getTime($dateTime, $gtm = false){
		if($dateTime !== false && !is_numeric($dateTime)){
			$dateTime = new DateTime($dateTime);
			if(false !== $dateTime){
                $offset = 0;
                if($gtm){
                    $offset = $dateTime->getOffset();
                }
				$dateTime = $dateTime->getTimestamp() + $offset;

			}
		}
		if($dateTime === false){
			$dateTime = time();
		}
		return $dateTime;
	}

	// Получаем период
	public static function getPeriod($toDate, $in = self::PERIOD_DAY, $fromDate = "now")
	{
		$periods = array(
			self::PERIOD_DAY  => 'days',
			self::PERIOD_HOUR => 'h',
			self::PERIOD_YEAR => 'y'
		);
		if (!array_key_exists($in, $periods)) {
			$in = self::PERIOD_DAY;
		}
		$fromDate = new DateTime(self::toMysql($fromDate, $in));
		$toDate   = new DateTime(self::toMysql($toDate, $in));
		return (int)(($fromDate->diff($toDate)->invert?'-':'').$fromDate->diff($toDate)->$periods[$in]);
	}

    //версия getPeriod с DB временем по дефолту
    public static function getPeriodDB($toDate, $in = self::PERIOD_DAY, $fromDate = null)
    {
        if (empty($fromDate))
            $fromDate = RService::getDbTimestamp();

        return self::getPeriod($toDate, $in, $fromDate);
    }


	public static function getPeriodHumanString($toDate, $in = self::PERIOD_DAY, $fromDate = "now")
	{
		$periodsTemplates = array(
			self::PERIOD_DAY => '{n} день|{n} дня|{n} дней',
			self::PERIOD_HOUR => '{n} час|{n} часа|{n} часов',
			self::PERIOD_YEAR => '{n} год|{n} года|{n} лет'
		);
		$period = self::getPeriod($toDate, $in, $fromDate);
		if($in == self::PERIOD_DAY){
			switch($period){
				case 0:
					$period = lng::t('сегодня');
					break;
				case 1:
					$period = lng::t('вчера');
					break;
				case 6:
					$period = lng::t("неделю назад");
					break;
				default:
					$period = lng::t($periodsTemplates[$in], $period);
			}
		}

		return $period;
	}

	public static function getAdaptivePeriodHumanString($toDate)
	{
		try {
			$fromDate = new DateTime(self::toMysql(RService::getDbTimestamp()));
			$toDate   = new DateTime($toDate);

			if($toDate->format('Y') <= 0 || $fromDate->format('Y') <= 0){
				throw new Exception();
			}
			$period   = $fromDate->diff($toDate);
			$invert = ($period->invert?lng::t('назад'):lng::t('осталось'));
			if($period->d > 0 || $period->m > 0 || $period->y > 0){
				return DateUtils::_date($toDate->format('U'), true, true, $toDate->format('Y') != date('Y'));
			}
			$todayPeriod = $toDate->diff(new DateTime(date('Y-m-d')));
			// добавляет день если, это было вчера, но при этом еще не прошли сутки
			$addDay = $todayPeriod->invert?0:1;
			switch($period->d+$addDay){
				case 0:
					if($period->h < 6 && $period->h > 0){
						return StringUtils::pluralEnd($period->h, array('%d час', '%d часа', '%d часов')).' '. $invert;
					}else if($period->h == 0){
						if ($period->i > 0) {
							return StringUtils::pluralEnd($period->i, array('%d минуту', '%d минуты', '%d минут')) . ' ' . $invert;
						}
						if ($period->s > 0) {
							return StringUtils::pluralEnd($period->s, array('%d секунду', '%d секунды', '%d секунд')) . ' ' . $invert;
						}
					}
					return lng::t('сегодня').', '.$toDate->format('H:i');
					break;
				case 1:
					return lng::t('вчера').', '.$toDate->format('H:i');
					break;
			}

		} catch (Exception $e) {
			return '';
		}
	}

	// В mySQL формат
	public static function toMysql($date=false, $in = self::PERIOD_HOUR)
	{
		$date = self::getTime($date);

		$pattern = self::$period_format[$in];//по умолчанию - 'yyyy-MM-dd HH:mm:ss'
		return Yii::$app->dateFormatter->format($pattern, $date);
	}

	// Из mySQL формата
	public static function fromMysql($date, $pattern = 'yyyy-MM-dd HH:mm:ss')
	{
		return CDateTimeParser::parse($date, $pattern);
	}

	// Из календаря
	public static function fromForm($date, $setTime = true, $pattern = 'dd.MM.yyyy')
	{
		return CDateTimeParser::parse($date.($setTime?' '.Yii::$app->dateFormatter->format('H:m:s', time()):''), $pattern.($setTime?' H:m:s':''));
	}

	// В календарь
	public static function toForm($date, $pattern = 'dd.MM.yyyy')
	{
		return Yii::$app->dateFormatter->format($pattern, $date);
	}

	// Возвращает период в часах
	public static function periodInHours($toDate, $fromDate = false)
	{
		if (!$fromDate)
			$fromDate = time();

		return ceil(($toDate - $fromDate) / 60 / 60);
	}

    // Возвращает период в днях
    public static function periodInDays($toDate, $fromDate = false)
    {
        $toDate = DateUtils::getTime($toDate);
        $fromDate = DateUtils::getTime($fromDate);
        return ceil(($toDate - $fromDate) / 86400);
    }

    // Возвращает период в днях, с помесячной разбивкой
    public static function periodInDaysByMonth($fromDate, $toDate)
    {
        $days = self::periodInDays($toDate, $fromDate);
        $result = array();

        $fromDate = DateUtils::getTime($fromDate);
        $toDate = DateUtils::getTime($toDate);


        for ($day=0; $day <= $days; $day++)
        {
            $date = $fromDate + ($day * 86400);
            $dateIdx = date("Y-m-01", $date);
            $result[$dateIdx] += 1;
        }

        return $result;
    }

	// Возвращает дату из кол-ва часов
	public static function hoursToDate($hours)
	{
		return time() + ($hours * 60 * 60);
	}

	// Возвращает дату из кол-ва дней
	public static function daysToDate($days, $from=false)
	{
		if(!(is_numeric($days) && $days >= 0)){
			$days = 0;
		}
		return self::getTime($from) + ($days * 24 * 60 * 60);
	}

	/**
	 * Возвращает формулировку кол-ва дней
	 * @param $date дата в формате MySQL или Unixtime
	 * @param $type тип - часы/дни
	 * @param $options Формат строки:
	 *  array(
	 *   $prefix = '',
	 *   $suffix = ' ',
	 *   $end = '',
	 *   $empty = ''
	 *  )
	 *
	 */
	public static function formatAmount($date, $type=false, $options=false, $absolute=false, $fromDate=NULL)
	{
		list($prefix, $suffix, $end, $empty) = is_array($options) ? $options : array('', ' ', '', 'отключен');

		$time = self::getTime($date);

		if (self::is_empty($fromDate))
            $current = time();
        else
            $current = self::getTime($fromDate);

		$diff = ($time - $current);
		if($absolute) $diff = abs($diff);
		$amount = ceil($diff/3600);

		if($type === false){
			$type = self::PERIOD_DAY;
		}elseif($type === true){
			if($amount < 24) $type = self::PERIOD_HOUR;
			else $type = self::PERIOD_DAY;
		}

		if($amount > 0){
			if($type == self::PERIOD_HOUR){
				$plural = array('час', 'часа', 'часов');
			}else{
				$amount = ceil($amount/24);
				$plural = array('день', 'дня', 'дней');
			}
		}elseif($amount <= 0)
			return $empty;

		return $prefix.$amount.$suffix.StringUtils::pluralEnd($amount, $plural).$end;
	}

	public static function getAmount($date, $type=false)
	{
		$type = $type === false ? self::PERIOD_DAY : $type;
		$time = self::getTime($date);
		$current = time();

		$amount = max(0, ceil(($time - $current)/3600));
		return $type == self::PERIOD_HOUR ? $amount : ceil($amount/24);
	}

    //версия с DB временем
    public static function getAmountDB($date, $type=false)
    {
        $type = $type === false ? self::PERIOD_DAY : $type;
        $time = self::getTime($date);
        $current = RService::getDbTimestamp();

        $amount = max(0, ceil(($time - $current)/3600));
        return $type == self::PERIOD_HOUR ? $amount : ceil($amount/24);
    }

    public static function getAmountPast($date, $type=false)
	{
		$type = $type === false ? self::PERIOD_DAY : $type;
		$time = self::getTime($date);
		$current = time();

		$amount = max(0, ceil(($current - $time)/3600));
		return $type == self::PERIOD_HOUR ? $amount : ceil($amount/24);
	}


	public static function _time($datetime = false)
	{
		$time = self::getTime($datetime);
		return date('H:i', $time);
	}

	// Прикольно форатирует mySQL дату
	public static function _date($datetime = false, $todayFormat = true, $time = true, $year = false)
	{
		if( $datetime == false )
			$datetime = date('Y-m-d H:i:s');

		if( is_numeric($datetime) )
			$datetime = date('Y-m-d '.($time?'H:i:s':'00:00:00'), $datetime);
		else
	   		$datetime = $datetime.((!$time AND !mb_strpos($datetime,'00:00:00')) ? '00:00:00' : '');

		if( !preg_match('#(([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}))?\s*(([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}))?#', trim($datetime), $p ) )
			return false;

		list(,, $y, $m, $d, , $h, $i, $s ) = array_map('intval', $p);

		$out = '';

        if ($m && $d) {
            $out = $d . ' ' . self::months($m) . ($y && ($y != date('Y') || $year) ? ' ' . $y : '');
            if ($todayFormat) {
                if ($d . '-' . $m . '-' . $y == date('j-n-Y')) {
                    $out = lng::t('Сегодня');
                } else if ($d . '-' . $m . '-' . $y == date('j-n-Y', strtotime('-1day'))) {
                    $out = lng::t('Вчера');
                }
            }
        }

		if( $time && ($h > 0 || ($h == 0 && $i)) )
			$out .= ($out ? ', ' : '').($h < 10 ? '0' : '').$h.':'.($i < 10 ? '0' : '').$i;

		return $out;
	}

	public static function toTimeStamp($date)
	{
		if (!strstr($date, ':'))
			return $date;
		else
			return strtotime($date);
	}

	public static function months($num = NULL)
	{
        if (Yii::$app->language == Yii::$app->sourceLanguage)
            return empty($num) ? self::$months : self::$months[$num];

        $lngArr = array_map(array('lng', 't'), self::$months);


        return empty($num) ? $lngArr : $lngArr[$num];
	}

	public static function months_wo_days($num = NULL)
	{
        if (Yii::$app->language == Yii::$app->sourceLanguage)
            return empty($num) ? self::$months_wo_days : self::$months_wo_days[$num];

        $lngArr = array_map(array('lng', 't'), self::$months_wo_days);

        return empty($num) ? $lngArr : $lngArr[$num];
	}

	public static function days()
	{
		$days = array();
		for($d = 1; $d <= 31; ++$d) $days[$d] = $d;

		return $days;
	}

	public static function years($from=false, $to=false, $ord=false)
	{
		if($from !== false){
			if($from < 1900) $from = date('Y') + $from;
		}else $from = 1940;

		if($to !== false){
			if($to < 1900) $to = date('Y') + $to;
		}else $to = date('Y');

		if($from > $to) $to = date('Y');

		$years = array();
		if($ord) for($y = $from; $y <= $to; ++$y) $years[$y] = $y;
		else for($y = $to; $y >= $from; --$y) $years[$y] = $y;

		return $years;
	}

    //имеется ли в строке даты часть со временем 00:00:00
    public static function hasTimePart($strDate='')
    {
        return mb_strlen(trim($strDate)) > 12 ? true : false;
    }

	/**
	 *  пустые даты разными бывают...
	 *	by bonerdelli
	 */

	public static function is_empty($date) {
		if ( empty($date) ) return true;
		else if ( $date == '0000-00-00 00:00:00' ) return true;
		else if ( $date == '1970-01-01 06:00:00' ) return true;
		else return false;
	}

    public static function nullTime()
    {
        return '0000-00-00 00:00:00';
    }

    public static function leadingZero($number)
    {
        return (intval($number) > 9)?intval($number):'0'.intval($number);
    }

    public static function toDateTimeLocal($value = null){
        if(empty($value) || intval($value) < 1){
            $value = time();
        }
        if(!is_numeric($value)){
            $value = strtotime($value);
        }
        return strftime('%Y-%m-%dT%H:%M', $value);
    }

	/**
	 * Добавить к дате рабочие дни
	 */
	public static function addWorkday($date=null,$days=3) {
		$result = date("Y-m-d", strtotime($date));
		$result_ = $result;
		$i = 0;
		while ($i < ($days)) {
			$result = date("Y-m-d", strtotime($result . " + $i days"));
			if (self::isWeekend($result_) === true)
				$result = date("Y-m-d", strtotime($result . " + 1 days"));
			$i++;
		}
		return $result;
	}

	//предыдущий рабочий день
    public static function decWorkday($date=null,$days=3) {
        $result = DateUtils::getTime($date);
        $i = $days;

        while ($i > 0) {
            $result = $result - 86400;
            if (self::isWeekend(DateUtils::toMysql($result)) === true) {
                $result = $result - 86400;
            }
            else
                $i--;
        }

        return DateUtils::toMysql($result);
    }

    /**
	 * праздничные дни в этом году
	 * @return array()
	 */
	public static function getHolidays() {

		$cacheKey = 'holidays_at_'.date("Y");
		$holidays = RService::getCache2L($cacheKey);
		if (!$holidays) {
			$holidays = array();
			$prc = self::getProductionCalendar();

			// days - праздники/короткие дни/рабочие дни (суббота либо воскресенье)
			// d - день (формат ММ.ДД)
			// t - тип дня: 1 - выходной день, 2 - короткий день, 3 - рабочий день (суббота/воскресен)
			// h - номер праздника (ссылка на атрибут id тэга holiday)
			// суббота и воскресенье считаются выходными, если нет тега day с атрибутом t=3 за этот день
			if (isset($prc->days))
				foreach ($prc->days->day as $day) {
					$attrs = $day->attributes();
					if ($attrs['t']==1)
						$type = 'holiday';
					else
						$type = 'workday';
					$holidays[] = array((string)$attrs['d']=>$type);
				}
			RService::setCache2L($cacheKey,$holidays,3600*30);
		}

		return $holidays;
	}

	public static function isWeekend($date_my) {//дата типа: date("d.m.Y") или "19.02.2011"
		$holidays = self::getHolidays();
		$check_date = date("m.d",strtotime($date_my));
		foreach ($holidays as $day) {
			if (isset($day[$check_date])){
				if ($day[$check_date] == 'holiday'){
					return true;
				}

				if ($day[$check_date] == 'workday')
					return false;
			}
		}

		$weekend = date("w",strtotime($date_my));
		if($weekend==0 || $weekend==6)
			return true;
		return false;
	}
	/**
	 * Производственный календарь
	 * @return SimpleXMLElement
	 */
	private static function getProductionCalendar() {
		// http://xmlcalendar.ru/
		$url = "http://xmlcalendar.ru/data/ru/".date("Y")."/calendar.xml";
		$data_raw = file_get_contents($url);
		$data = new SimpleXMLElement($data_raw);

		if (count($data))
			return $data;

		return false;
	}

	public static function isFirstWorkDay($date, $timepart=null)
    {
        $day = date("d", DateUtils::getTime($date));

        if ( $day > 14) return false;

        $month = date("n", DateUtils::getTime($date));
        $year = date("Y", DateUtils::getTime($date));


        if ($month == date("n", DateUtils::decWorkday($date,1)))
            return false;

        $date2 = $year."-".$month."-".$day." ".$timepart;
        if (!empty($timepart) && DateUtils::getTime($date) > DateUtils::getTime($date2))
            return false;

        return true;
    }

	public static function age($birthday, $date = null, $asString = false){
		if (empty($date)){
			$date = time();
		} else {
			$date = strtotime($date);
		}

		$bDate = strtotime($birthday);

		$y = intval(date("Y", $date)) - intval(date("Y", $bDate));
		$m = intval(date("m", $date)) - intval(date("m", $bDate));
		$d = intval(date("d", $date)) - intval(date("d", $bDate));

		if ($d < 0){
			$m--;
			$d = date("t") + $d;
		}

		if ($m < 0){
			$y--;
			$m = 12 + $m;
		}


		if ($asString){
			$out = [];
			if ($y > 0)
				$out[] = $y." ".\app\components\StringUtils::pluralEnd($y, ['год','года','лет']);

			if ($m > 0)
				$out[] = $m.' мес';

			if ($y == 0 && $m ==0)
				$out[] = $d." ".\app\components\StringUtils::pluralEnd($d, ['день','дня','дней']);

			return implode(' ', $out);

		} else return [
				'y' => $y,
				'm' => $m,
				'd' => $d
			];
	}
}
