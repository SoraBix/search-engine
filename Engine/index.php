<?php

ini_set('memory_limit', '-1');

include 'SpellCorrector.php';
include 'snippets.php';

header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$correctionFlag = false;

if ($query)
{
	require_once('Apache/Solr/Service.php');

	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/homework/');

	if (get_magic_quotes_gpc() == 1)
	{
		$query = stripslashes($query);
		$query = preg_replace('!\s+!', ' ', trim(strtolower($query)));
	}

	$file = fopen('UrlToHtml_NBCNews.csv', 'r');
  
	if(!$file)
	{
		die('ERROR!');
	}

	$mapping = array();
  
	while($row = fgetcsv($file))
	{
		$mapping[$row[0]] = $row[1];
	}

	fclose($file);

	try
	{
		$additionalParameters = ($_REQUEST['algo'] == 'pagerank') ? array('sort' => 'pageRankFile desc') : array('sort' => 'score desc');

		$correction = '';
		$terms = explode(' ', $query);

		foreach($terms as $term)
		{
			$correct = SpellCorrector::correct($term);
			$correction .= $correct . ' ';
		}
	
		$correctedQuery = trim($correction);

		if (!(strtolower($correctedQuery) === strtolower($query)))
		{
			$correctionFlag = true;
		}

		$searchQuery = $query;

		$results = $solr->search($searchQuery, 0, $limit, $additionalParameters);
	}
	catch (Exception $e)
	{
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
}

?>

<html>
	<head>
		<title>CSCI 572</title>
		<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
		<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<script src="autocomplete.js"></script>
	</head>
	<body>
		<form  accept-charset="utf-8" method="get">
			<label for="q">Search:</label>
			<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
			<input type="radio" name="algo" value="lucene" <?php if($_REQUEST['algo'] != "pagerank") echo "checked"; ?> >Lucene
			<input type="radio" name="algo" value="pagerank" <?php if($_REQUEST['algo'] == "pagerank") echo "checked"; ?> >PageRank
			<input type="submit"/>
		</form>

<?php

if($correctionFlag)
{

?>

		<div id='correction_display'>

<?php

	$algo = $_REQUEST['algo'];

?>

		<div>Did you mean <b><i><a href="index.php?q=<?php echo str_replace(" ", "+", $correctedQuery); ?>&algo=<?php echo $algo; ?>"><?php echo $correctedQuery; ?></a></i></b></div>
		</div>
		<br>

<?php

}

?>

<?php

if($results)
{
	$total = (int) $results->response->numFound;
	$start = min(1, $total);
	$end = min($limit, $total);

?>

		<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
			<ol>

<?php

	foreach ($results->response->docs as $doc)
	{
		if (array_key_exists('og_url', $doc))
		{
			$url = $doc->og_url;
		}
		else
		{
			$key = str_replace("/media/sf_Shared/NBC_News/HTML files/", "", $doc->id);
			$url = $mapping[$key];
		}

?>
				<li>
					<table style="border: 1px solid black; text-align: left">
						<tr>
							<th><?php echo htmlspecialchars('Title', ENT_NOQUOTES, 'utf-8'); ?></th>
							<td><a href= <?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?> ><?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?></a></td>
						</tr>

						<tr>
							<th><?php echo htmlspecialchars('URL', ENT_NOQUOTES, 'utf-8'); ?></th>
							<td><a href= <?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?> ><?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?></a></td>
						</tr>

						<tr>
							<th><?php echo htmlspecialchars('ID', ENT_NOQUOTES, 'utf-8'); ?></th>
							<td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
						</tr>

						<tr>
							<th><?php echo htmlspecialchars('Desc', ENT_NOQUOTES, 'utf-8'); ?></th>
							<td><?php echo generateSnippet($doc->id, $searchQuery); ?></td>
						</tr>
					</table>
				</li>

<?php

	}

?>

			</ol>

<?php

}

?>

	</body>
</html>