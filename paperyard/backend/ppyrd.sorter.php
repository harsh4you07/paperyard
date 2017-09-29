<html>
<body>
	<pre>
<?php
	require_once('dbHandler.php');
	require_once('helper.php');


	/**
	 * \brief Sorts thru PDF documents and puts them into corresponding folders etc.
	 * @param none
	 * @return none
	 */
	class pdfSorter {

		/**
		 * \brief constructor
		 * @param string $pdf file name to be processed
		 * @param string $db database handler
		 * @return none
		 */
		public function __construct($pdf, $db)
		{
				$this->pdf = $pdf;

				// creating db handler to talk to DB
				$this->db=$db;
		}

		/**
		 * \brief function gets from file name the information what the date, company and subject is.
		 */
		function splitUpFilename()
		{
			$this->output("working on: " . $this->pdf);

			// getting the file name template - needed to see what part (date, company, subject) is at which part of the string
			$newFilenameStructure=$this->db->getConfigValue('newFilenameStructure');

			// getting things via regex
			// removing closing parts of the brackets as they are not needed in this case
			$unwanted = array(')',']');
			$separators = "/ - | \(| \[| \-\- /";
			$templateName = str_replace($unwanted, '', $newFilenameStructure);

			// splitting up template name into parts
			$templateParts = array_flip(preg_split($separators, $templateName));

			// now doing the same stuff with the actual file
			$tmpName = str_replace($unwanted, '', $this->pdf);
			$filenameParts = preg_split($separators, $tmpName);



			// separating the file name into its parts
			$parts = explode(" - ", $this->pdf);

			// date is 1st
			$this->date = $filenameParts[$templateParts['ddatum']];
			$this->year = date('Y',strtotime($this->date));
			$this->month = date('m',strtotime($this->date));
			$this->day = date('d',strtotime($this->date));

			// company etc.
			$this->company = $filenameParts[$templateParts['ffirma']];
			$this->recipient = $filenameParts[$templateParts['wwer']];
			$this->subject = $filenameParts[$templateParts['bbetreff']];
			$this->amount = $filenameParts[$templateParts['bbetrag']];



			// getting all tags @todo - needs to be least greedy ...
			preg_match_all('/\[(\d|\w)*\]/',$this->pdf,$tags);
			$this->tags = implode($tags[0]);

			//
			$this->output( "date: " . $this->date);
			$this->output( "year: " . $this->year);
			$this->output( "month: " . $this->month);
			$this->output( "day: " . $this->day);

			$this->output( "company: " . $this->company);
			$this->output( "recipient: " . $this->recipient);
			$this->output( "subject: " . $this->subject);
			$this->output( "amount: " . $this->amount);
			$this->output( "tags: " . $this->tags);

		}

		/**
		 * \brief Output formatter
		 * @param string $string what to output
		 * @debug int $debug to specify if debug or not
		 */
		function output($string, $debug=0)
		{
					echo "$string\n";
		}

		/**
		 * \brief checks rules
		 */
		function checkRules()
		{
			$rules = $this->db->getActiveArchiveRules();
			while ($row = $rules->fetchArray()) {
				// we have  a rule match if the company found matches the specified string
				// * is the wild card like in file names
				$match = fnmatch($row['company'], $this->company)
								&& fnmatch($row['subject'], $this->subject)
								&& fnmatch($row['recipient'], $this->recipient)
								&& fnmatch($row['tags'], $this->tags);

				// if everything matched - go ahead
				if ($match) {
						// processing the folder to which document shall be moved
						$toFolder = $row['toFolder'];

						// in case the [year] variable has been used etc.
						$toFolder = str_replace('[year]', $this->year, $toFolder);
						$toFolder = str_replace('[month]', $this->month, $toFolder);
						$toFolder = str_replace('[day]', $this->day, $toFolder);
						$toFolder = str_replace('[recipient]', $this->recipient, $toFolder);

						// adding a trailing slash in case none existed
						$toFolder = rtrim($toFolder, '/') . '/';

						// create folders in case required
						exec("mkdir -p $toFolder");

						// move the file to destination folder
						exec('mv --backup=numbered "' . $this->pdf . '" "' . $toFolder . $this->pdf . '"');

						$this->db->writeLog($this->pdf, $this->pdf, "", "Moved file to: " . $toFolder);
				}
			}
		}

		/**
		 * \brief runs the main process
		 */
		function run()
		{

			// process the file name first
			$this->splitUpFilename();

			// then see if there is any rule to process
			$this->checkRules();
		}
	}


// main program
// looping main directory and calling the pdf parser
echo "starting paperyard\n";

/**
 * creating db handler to talk to DB
 */
$db=new dbHandler();


/**
 * checking if called via command line or webserver
 * @cond */
if ("cli" == php_sapi_name())
	{
    echo "Program call from CLI detected.\n";
		if ($db->getConfigValue('enableCron')==0) {
				echo "please enable cron in config\n";
				die();
			}
		}else{
    echo "Program call from Webserver detected.\n";
}
/** @endcond */


// creating folder structure in case it does not exist
exec('mkdir -p /data/scan');
exec('mkdir -p /data/scan/error');
exec('mkdir -p /data/scan/archive');
exec('mkdir -p /data/inbox');
exec('mkdir -p /data/outbox');
exec('mkdir -p /data/sort');

echo "calling the sorter ... \n";
chdir("/data/sort");

/**
 * reading all pdf files from current directory
 */
$pdfs = glob("*.pdf");

/**
 * loops thru all found pdfs and puts the individual pdf name into pdf variable
 */
foreach ($pdfs as $pdf){
    $pdf=new pdfSorter($pdf, $db);
		$pdf->run();
}

echo "\n";
/*******************************************************************************/

echo "\n Thanks for watching....\n\n";

$db->close();

?>
	</pre>
</body>
</html>