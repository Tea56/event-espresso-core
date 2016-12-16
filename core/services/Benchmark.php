<?php
namespace EventEspresso\core\services;

defined('ABSPATH') || exit;



/**
 * Class Benchmark
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class Benchmark
{

    /**
     * array containing the start time for the timers
     */
    private static $start_times;

    /**
     * array containing all the timer'd times, which can be outputted via show_times()
     */
    private static $times = array();

    /**
     * @var array
     */
    protected static $memory_usage = array();



    /**
     * reset_times
     */
    public static function doNotRun()
    {
        return WP_DEBUG !== true || (defined('DOING_AJAX') && DOING_AJAX);
    }



    /**
     * reset_times
     */
    public static function resetTimes()
    {
        Benchmark::$times = array();
    }



    /**
     *    start_timer
     *
     * @param null $timer_name
     */
    public static function startTimer($timer_name = null)
    {
        if (Benchmark::doNotRun()) {
            return;
        }
        $timer_name = $timer_name !== '' ? $timer_name : get_called_class();
        Benchmark::$start_times[$timer_name] = microtime(true);
    }



    /**
     * stop_timer
     *
     * @param string $timer_name
     */
    public static function stopTimer($timer_name = '')
    {
        if (Benchmark::doNotRun()) {
            return;
        }
        $timer_name = $timer_name !== '' ? $timer_name : get_called_class();
        if (isset(Benchmark::$start_times[$timer_name])) {
            $start_time = Benchmark::$start_times[$timer_name];
            unset(Benchmark::$start_times[$timer_name]);
        } else {
            $start_time = array_pop(Benchmark::$start_times);
        }
        Benchmark::$times[$timer_name] = number_format(microtime(true) - $start_time, 8);
    }



    /**
     * Measure the memory usage by PHP so far.
     *
     * @param string  $label      The label to show for this time eg "Start of calling Some_Class::some_function"
     * @param boolean $output_now whether to echo now, or wait until EEH_Debug_Tools::show_times() is called
     * @return void
     */
    public static function measureMemory($label, $output_now = false)
    {
        if (Benchmark::doNotRun()) {
            return;
        }
        $memory_used = Benchmark::convert(memory_get_peak_usage(true));
        Benchmark::$memory_usage[$label] = $memory_used;
        if ($output_now) {
            echo "\r\n<br>$label : $memory_used";
        }
    }



    /**
     * show_times
     *
     * @param bool $echo
     * @return string
     */
    public static function displayResults($echo = true)
    {
        if (Benchmark::doNotRun()) {
            return '';
        }
        $output = '';
        $margin = is_admin() ? ' margin:2em 2em 2em 180px;' : ' margin:2em;';
        if ( ! empty(Benchmark::$times)) {
            $total = 0;
            $output .= '<div style="border:1px solid #dddddd; background-color:#ffffff;' . $margin . ' padding:2em;">';
            $output .= '<h4>BENCHMARKING</h4>';
            $output .= '<span style="color:#999999; font-size:.8em;">( time in milliseconds )</span><br />';
            foreach (Benchmark::$times as $timer_name => $total_time) {
                $output .= Benchmark::formatTime($timer_name, $total_time) . '<br />';
                $total += $total_time;
            }
            $output .= '<br />';
            $output .= '<h4>TOTAL TIME</h4>';
            $output .= Benchmark::formatTime('', $total);
            $output .= '<span style="color:#999999; font-size:.8em;"> milliseconds</span><br />';
            $output .= '<br />';
            $output .= '<h5>Performance scale (from best to worse)</h5>';
            $output .= '<span style="color:mediumpurple">Like wow! How about a Scooby snack?</span><br />';
            $output .= '<span style="color:deepskyblue">Like...no way man!</span><br />';
            $output .= '<span style="color:limegreen">Like...groovy!</span><br />';
            $output .= '<span style="color:gold">Ruh Oh</span><br />';
            $output .= '<span style="color:orange">Zoinks!</span><br />';
            $output .= '<span style="color:red">Like...HEEELLLP</span><br />';
        }
        if ( ! empty(Benchmark::$memory_usage)) {
            $output .= '<h5>Memory</h5>' . implode('<br />', Benchmark::$memory_usage);
        }
        $output .= '</div><br />';
        if ($echo) {
            echo $output;
            return '';
        }
        return $output;
    }


    /**
     * Converts a measure of memory bytes into the most logical units (eg kb, mb, etc)
     *
     * @param int $size
     * @return string
     */
    public static function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, $i = floor(log($size, 1024))), 2) . ' ' . $unit[absint($i)];
    }




    /**
     * @param string $timer_name
     * @param float  $total_time
     * @return string
     */
    public static function formatTime($timer_name, $total_time)
    {
        $total_time *= 1000;
        switch ($total_time) {
            case $total_time > 6250 :
                $color = 'red';
                $bold = 'bold';
                break;
            case $total_time > 1250 :
                $color = 'orange';
                $bold = 'bold';
                break;
            case $total_time > 250 :
                $color = 'yellow';
                $bold = 'bold';
                break;
            case $total_time > 50 :
                $color = 'limegreen';
                $bold = 'normal';
                break;
            case $total_time > 10 :
                $color = 'deepskyblue';
                $bold = 'normal';
                break;
            default :
                $color = 'mediumpurple';
                $bold = 'normal';
                break;
        }
        return '<span style="min-width: 10px; margin:0 1em; color:'
               . $color
               . '; font-weight:'
               . $bold
               . '; font-size:1.2em;">'
               . str_pad(number_format($total_time, 3), 9, '0', STR_PAD_LEFT)
               . '</span> '
               . $timer_name;
    }

}
// End of file Benchmark.php
// Location: EventEspresso\core\services/Benchmark.php