<?php
/**
 * Helper class for Next Event module
 * 
 * @package    Next Event
 * @subpackage Modules
 * @link http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * @license        GNU/GPL, see LICENSE.php
 * mod_nextevent is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
class ModNextEventHelper
{
    /**
     * Retrieves the event name
     *
     * @param   array  $params An object containing the module parameters
     *
     * @access public
     */    
    public static function getNextEvent($params)
    {
        // Retrieve the date format
        $dateformat = $params->get('dateformat');
        $eventname = $params->get('eventname');
        $unscheduled = $params->get('unscheduled');
        $inthefuture = $params->get('inthefuture');

        // Obtain a database connection
        $db = JFactory::getDbo();

        // Retrieve the not-until date. This is actual a delta time and we have to compute when to start showing something.
        $within = $params->get('within');

        // Get the actual date of the event
        $found = true;
        if ($within == "")
            $notifydate = "now()";
        else
        {
            $query = "SELECT DATE_ADD(startrepeat,$within) as notifydate FROM #__jevents_vevdetail e JOIN #__jevents_repetition r ON e.evdet_id=r.eventdetail_id WHERE startrepeat > now() AND summary='$eventname' ORDER BY startrepeat LIMIT 1";
            $db->setQuery($query);
            $notifydate = $db->loadResult();
            if ($notifydate == "")
            {
                $found = false;
                $notifydate = "now()";
            }
        }

        // Now see if we're within the specified interval
        $query = "SELECT date_format(startrepeat,'$dateformat') as nextdate FROM #__jevents_vevdetail e JOIN #__jevents_repetition r ON e.evdet_id=r.eventdetail_id WHERE startrepeat > now() AND '$notifydate' <= now() AND summary='$eventname' ORDER BY startrepeat LIMIT 1";
        $db->setQuery($query);
        $nextdate = $db->loadResult();

        // If no such event, return the specified message.
        if (!$nextdate)
        {
            if ($found)
                $nextdate = $inthefuture;
            else
                $nextdate = $unscheduled;
        }

        // Return the date
        return $nextdate;
    }
}
?>