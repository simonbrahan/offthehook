<?php
$support_staff = array(
    'amber',
    'imran',
    'jenny'
);

define('DAYS_IN_FUTURE', 13);

$date_started = new DateTime('2016-03-23');

$date_to_show = new DateTime('today');

$bank_holidays = getBankHolidays($date_to_show, DAYS_IN_FUTURE);

if (!$bank_holidays) {
    $bank_holidays = array();
}

$rota_position = (int) $date_started->diff($date_to_show)->format('%a');

$candidate_dates = new DatePeriod(
    $date_to_show,
    new DateInterval('P1D'),
    DAYS_IN_FUTURE
);

$dates = array();

foreach ($candidate_dates as $date_to_show) {
    if (rotaIsSuspended($date_to_show, $bank_holidays)) {
        $dates[] = array(
            'date' => $date_to_show,
            'message' => 'Rota suspended',
            'type' => 'noone'
        );

        continue;
    }

    $off_the_hook_today = $support_staff[$rota_position%count($support_staff)];

    $dates[] = array(
        'date' => $date_to_show,
        'message' => $off_the_hook_today . ' is off the hook today',
        'type' => 'someone'
    );

    $rota_position ++;
}

function isOnWeekend(\DateTime $date)
{
    return (int) $date->format('N') > 5;
}

function getBankHolidays(\DateTime $start, $num_days)
{
    $response = file_get_contents('https://www.gov.uk/bank-holidays.json');

    if (!$response) {
        return false;
    }

    $bank_holidays = json_decode($response);

    if (!$bank_holidays) {
        return false;
    }

    if (!$bank_holidays->{'england-and-wales'} || !$bank_holidays->{'england-and-wales'}->events) {
        return false;
    }

    $end = new DateTime();
    $end->add(new DateInterval('P' . $num_days . 'D'));

    $applicable_bank_holidays = array();
    foreach ($bank_holidays->{'england-and-wales'}->events as $bank_holiday) {
        $holiday_date = new DateTime($bank_holiday->date);

        if ($holiday_date >= $start && $holiday_date <= $end) {
            $bank_holiday->date = $holiday_date;
            $applicable_bank_holidays[] = $bank_holiday;
        }

        /**
         * Holidays are given in order; stop searching once we are past what we are showing
         */
        if ($holiday_date > $end) {
            break;
        }
    }

    return $applicable_bank_holidays;
}

function rotaIsSuspended(\DateTime $date, $bank_holidays)
{
    if (isOnWeekend($date)) {
        // Date falls on a weekend
        return true;
    }

    foreach ($bank_holidays as $holiday) {
        if ($date == $holiday->date) {
            // Date falls on a bank holiday
            return true;
        }
    }

    return false;
}
