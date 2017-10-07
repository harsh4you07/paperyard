<?php
	/**
		* @file
		* \author Till Witt
		* \brief paperyard base class containing all relevant functions
		* \bug certainly still a lot
		*
	 	*/
	class ppyrd {
		/*!
			* \class helper
			* \brief containers helper functions
			* \author Till Witt
			* \bug no error handling implemented yet
			*
		 	* \details database handler
		 	*/
		var $db;

		/**
		 * \brief constructor
		 */
		function __construct() {
			$this->output("checking setup");
			$warning = array();
			if (file_exists('/data/scan/paperyardDirectoryNotMounted.txt'))
			{
				$warning[]= "scan folder not properly mounted. Please specify /data/scan in docker run command.\n";
			}
			if (file_exists('/data/inbox/paperyardDirectoryNotMounted.txt'))
			{
				$warning[]= "inbox folder not properly mounted. Please specify /data/inbox in docker run command.\n";
			}
			if (file_exists('/data/outbox/paperyardDirectoryNotMounted.txt'))
			{
				$warning[]= "outbox folder not properly mounted. Please specify /data/outbox in docker run command.\n";
			}
			if (file_exists('/data/sort/paperyardDirectoryNotMounted.txt'))
			{
				$warning[]= "sort folder not properly mounted. Please specify /sort/outbox in docker run command.\n";
			}
			if (file_exists('/data/database/paperyardDirectoryNotMounted.txt'))
			{
				$warning[]= "database folder not properly mounted. Please specify /data/database in docker run command.\n";
			}
			$this->warning = $warning;
			$warningMessage = join($warning, "");
			if (!empty($warning))
				echo "docker run not properly set:" . $warningMessage;

		} // End constructor



		/**
		 * \brief outputs string
		 * \bug no debug handling implemented yet. https://github.com/tlwt/paperyard/issues/10
		 * @param string $string to output
		 * @param int $debug set to 1 to debug
		 */
		function output($string, $debug=0)
		{
					echo "$string\n";
		}

	}

?>
