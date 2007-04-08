<?php
	/**
	 * The error configuration file
	 *
	 * @package machine
	 */
	class ErrorConfig {
		/**
		 * From which error level on should we handle it?
		 */
		// const LEVEL = E_USER_ERROR;  // 2039 for E_ALL &~ E_NOTICE
		const LEVEL = ERROR_HANDLE_LEVEL;  // 2039 for E_ALL &~ E_NOTICE
		/**
		 * Display errors/exceptions or not?
		 */
		const DISPLAY = ERROR_DISPLAY;
		
		
		/**
		 * should the script continue despite som error
		 *
		 */
		const LEVEL_CONTINUE_SCRIPT = ERROR_LEVEL_CONTINUE_SCRIPT; //  (E_WARNING ^ E_NOTICE);
		
		/*
		 * Display an error for user
		 */
		const DISPLAY_USER = ERROR_DISPLAY_USER;
		
		/**
		 * Encoded logfile of unique errors (from there we will know which errors
		 * already happened so they don't get mailed or logged again and again)
		 * NOTICE: You will need this if you're using logging or e-mails
		 */
		const UNIQUE_LOGFILE = ERROR_LOG_UNIQUE;
		//const UNIQUE_LOGFILE = ERROR_LOG_UNIQUE;
		
		/**
		 * Error logfile ('' for no log)
		 */
		const LOGFILE = ERROR_LOG;
		//const LOGFILE = '';
		
				
		/**
		 * Where should error/exception reports be sent to? ('' for no mailing)
		 */
		// const EMAIL_ADDRESS = ERROR_REPORT_EMAIL;
		const EMAIL_ADDRESS = '';		
	}
?>