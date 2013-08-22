<?php 
/**
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 * 
 * Typeframe-ized by Charlie Powell on 2010.07.26 from CAE2.
 */

//This is a hack to allow classes to still be available after the page has been rendered.
register_shutdown_function("session_write_close");

/**
 * Kudos to Rich Smith <http://www.jamsoft.biz/> for the original concept I found
 *  a while back.
 * He submitted a script onto Devshed <http://www.devshed.com>
 * labled "Storing PHP Sessions in a Database" 
 * <http://www.devshed.com/c/a/PHP/Storing-PHP-Sessions-in-a-Database/>.
 *
 */

if(!defined('REMOTE_IP')){
	define('REMOTE_IP', $_SERVER['REMOTE_ADDR']);
}

class Session{
  public static $sid;
  public static $uid = 0;
  public static $ttl;
  
  private static $instance = null;
  
  private function __construct(){
    
    //Session::$ttl = ConfigHandler::getValue('session', 'ttl');
    Session::$ttl = defined('SESSION_TTL')? SESSION_TTL : 3600;
    
    // Set the save handlers
    session_set_save_handler(
            array('Session', "start"),
            array('Session', "end"),
            array('Session', "read"),
            array('Session', "write"),
            array('Session', "destroy"),
            array('Session', "gc")
            );  
            
    Session::gc();
    
    // Only start a new session if there is not already one.
    if(!isset($_SESSION)){
      session_start();
    }
    // Possibly give a notice or something if it's started previously... or maybe not, shrug.
    
  }
  
  public static function singleton(){
    if(is_null(Session::$instance)){
      Session::$instance = new Session();
    }
    
    return Session::$instance;
  }
  
  public static function getInstance(){
    return Session::singleton();
  }

  public static function start($save_path, $session_name) {
    Session::$sid = session_id();
    
    //This is a core PHP function.  Save that a session has been started in the default server log.
    //error_log('Starting Session ' . $session_name . " ". $this->sid);//DEBUG//
    
    
    /*
     * Get the userID of the saved session (if exists).
     * If the query returns no valid rows (ie: this is a NEW session),
     * $data will be a blank array, thus never tripping the foreach and preserving $this->uid as FALSE.
     */
    $rs = Typeframe::Database()->prepare(
      "SELECT `uid`
      FROM #__sessions
      WHERE `sid` = ? AND `ip_addr` = ? LIMIT 1"
    );
    $rs->execute(Session::$sid, REMOTE_IP);
    
    /**
     * The session is NEW.  Create it.
     */
    if($rs->recordcount() == 0){
      $rs2 = Typeframe::Database()->prepare(
        "INSERT INTO #__sessions
        (`sid`, `ip_addr`, `uid`, `expires`)
        VALUES
        (?, ?, ?, ?)"
      );
      $rs2->execute(Session::$sid, REMOTE_IP, 0, Session::$ttl + time());
    }
    /**
     * The session already exists, just update the timestamp.
     */
    else{
    	$data = $rs->fetch_array();
    	Session::$uid = $data['uid'];
      
      $rs2 = Typeframe::Database()->prepare(
        "UPDATE #__sessions
        SET `expires` = ?
        WHERE `sid` = ? AND `ip_addr` = ?"
      );
      $rs2->execute(Session::$ttl + time(), Session::$sid, REMOTE_IP);
    }
  }
  
  public static function end() {
    // Nothing needs to be done in this function
    // since we used persistent connection.
  }
  
  public static function read( $id ) {  
    
    // Fetch session data from the selected database    
    $rs = Typeframe::Database()->execute("SELECT `session_data` FROM #__sessions WHERE `sid` = '$id' AND `ip_addr` = '" . REMOTE_IP . "'");
    $data = $rs->fetch_array();
    $data = $data['session_data'];
    
    if(!$data) $data = '';
    
    return $data;
  }
  
  public static function write( $id, $data ) {
    //CAELogger::write('Writing to the session...' . $data, 'debug', 'debug');
    
    Typeframe::Database()->prepare("UPDATE #__sessions SET session_data = ? WHERE `sid` = ? AND `ip_addr` = ?")->execute($data, $id, REMOTE_IP);
    
    return TRUE;
  }
  
  public static function destroy( $id = null) {
    
    if(is_null($id)) $id = Session::$sid;
    // Build query
    Typeframe::Database()->execute("DELETE FROM `#__sessions` WHERE `sid` = '$id'");
    
    return TRUE;
  }
  
  public static function gc() {
    /**
     * Delete ANY session that has expired.
     */
    Typeframe::Database()->prepare("DELETE FROM #__sessions WHERE `expires` < ?")->execute(time());
    
    // Always return TRUE
    return true;
  }

  /**
   * Saves the userID in the session database for logging in (or out) the user.
   *
   * @param int $uid
   */
  public static function SetUID($uid){
    Typeframe::Database()->prepare(
      "UPDATE `#__sessions`
      SET `uid` = ?
      WHERE `sid` = ? AND `ip_addr` = ?
      LIMIT 1 ;")->execute($uid, Session::$sid, REMOTE_IP);
  }
}

//HookHandler::RegisterHook('libraries_loaded', 'Session::singleton');
?>
