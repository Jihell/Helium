<?php
/**
 * Indique dans quel ordre on doit appeler les sections de mise en page avec
 * \He\Dispatch
 * 
 * =============================================================================
 * USAGE
 * =============================================================================
 * 
 * \He\Dispatch::bindAtBegin([class : string[, load on ajax : bool = false]]);
 * \He\Dispatch::bindAtLast([class : string[, load on ajax : bool = false]]);
 * 
 * Exemple
 * \He\Dispatch::bindAtBegin('namespacedelaclass\nomdelaclass', false);
 * \He\Dispatch::bindAtBegin('namespacedelaclass\nomdelaclasssuivante', false);
 * \He\Dispatch::bindAtLast('namespacedelaclass\nomdelaclassfinal', false);
 * \He\Dispatch::bindAtLast('namespacedelaclass\nomdelaclassfinalsuivante', true);
 */

\He\Dispatch::bindAtBegin('\Module\Layout\Header');
\He\Dispatch::bindAtBegin('\Module\Layout\Login');
\He\Dispatch::bindAtBegin('\Module\Layout\Menu');
\He\Dispatch::bindAtLast('\Module\Layout\Footer');