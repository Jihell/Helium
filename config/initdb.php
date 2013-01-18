<?php
/**
 * Assigne les paramètres à HeDB
 * 
 * =============================================================================
 * USAGE
 * =============================================================================
 * 
 * DB::bindTable(DB\Param::make()->table('matable'));
 * DB::bindTable(DB\Param::make()->table('matable2'));
 * DB::bindTable(DB\Param::make()->table('matable3'));
 * DB::bindTable(DB\Param::make()->table('matable4')->readOnly(true));
 * 
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He;

/** ============================================================================
 * Tables interne à Helium
 ============================================================================ */
/* Tables de localisation */
DB::bindTable(DB\Param::make()->table('he_lang'));
DB::bindTable(DB\Param::make()->table('he_localise'));
DB::bindTable(DB\Param::make()->table('he_lang_cat'));
/* Table de log super user */
DB::bindTable(DB\Param::make()->table('he_su_log'));
DB::bindTable(DB\Param::make()->table('he_trace_log'));

/** ============================================================================
 * Table du projet
 ============================================================================ */
DB::bindTable(DB\Param::make()->table('test')
							  ->bindJoinAlias('chat', 'id_cat', 'cat'));
DB::bindTable(DB\Param::make()->table('cat')
							  ->bindDependance('test', 'id_cat'));
DB::bindTable(DB\Param::make()->table('user'));