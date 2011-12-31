<?php

require_once 'config.php';
require_once 'google_api/apiClient.php';
require_once 'google_api/contrib/apiPlusService.php';

class PlusComments {
	protected $plusApi;
	protected $comments;
	protected $activityId;

	/**
	 * Constructor of the class.
	 *
	 * \param activityId The ID of a Google+ activity which contains the 
	 * comments that should be fetched by the class. May throw an exception
	 * if an error occurs.
	 */
	function __construct($activityId) {
		$this->activityId = $activityId;

		// Initialize Google+ API
		try {
			$apiClient = new apiClient();
		    $apiClient->setDeveloperKey(PLUS_API_KEY);
		    $this->plusApi = new apiPlusService($apiClient);

		    // Fetch comments for the given activity
		    $query = $this->plusApi->comments->listComments(
			    $activityId, 
			    array(
				    'maxResults' => 100, 
			    	'fields' => 'items(actor(displayName,image,url),object/content,published),nextPageToken'
			    )
			);

	    	$this->comments = isset($query->items) ? $query->items : array();

	    	// Fetch additional comments (if present)
	    	while (isset($query->nextPageToken)) {
	    		$query = $this->plusApi->comments->listComments(
		    		$activityId, 
		    		array(
			    		'maxResults' => 100, 
			    		'pageToken' => $query->nextPageToken,
			    		'fields' => 'items(actor(displayName,image,url),object/content,published),nextPageToken'
			    	)
		    	);

	    		$this->comments = array_merge(
		    		$this->comments, 
		    		isset($query->items) ? $query->items : array()
		    	);
	    	}

	    } catch(apiServiceException $e) {
	    	// Create a special comment that shows the error message (looks
	    	// better than some weird unformatted exception message, doesn't
	    	// it?)
	    	$this->comments[] = new Comment(array(
	    		'published' => date('c'),
	    		'object' => array(
	    			'content' => 'The comments for this post could not be fetched. Please check if you specified the correct activityId and API key.'
		    	),
		    	'actor' => array(
		    		'displayName' => 'Error!',
		    		'url' => '#',
		    		'image' => array(
		    			'url' => ERROR_IMAGE
			    	)
			    )
		    ));
	    }
	}

	/**
	 * Fetches the URL which points to the previously specified activity by 
	 * first looking if the URL is cached locally and if not, by querying the
	 * Google+ API.
	 *
	 * \return A string containing an URL which points to the activity
	 */
	function getActivityUrl() {
		// Check if URL is cached
		$cache = file_get_contents('url_cache.dat');
		
		$matches = array();
		if (preg_match('/"'.$this->activityId.'","([^"]*)"/', $cache, $matches) === 1) {
			return $matches[1];
		}

		// If we are here, the URL has not been cached yet, so lets get it
		// through the API
		try {
			$url = $this->plusApi->activities->get($this->activityId)->url;
		} catch (apiServiceException $e) {
			// This should NOT happen
			return "";
		}

		// Rudimentary check if we have a valid URL and not an error message or
		// something else
		if (preg_match("/https?:\/\/.*/i", $url) === 1) {
			// Write the URL into the cache
			$cache = sprintf("\"%s\",\"%s\"\n", $this->activityId, $url).$cache;

			// Note: I insert the URL at the beginning of the file because an
			// unknown URL tends to be for a very recent activity. Furthermore
			// recent activities tend to be requested more often than older
			// ones. So all in all, the preg_match above has to search less if
			// recent URLs are inserted at the top of the cache file
			file_put_contents('url_cache.dat', $cache);

			return $url;	
		}

		// If we are here, something went very wrong
		return "";
	}

	/**
	 * Used to get the comments as raw objects
	 * 
	 * \return An array containing objects of the class Comment, specified in
	 * the google-api-php-client. May be empty if no comments are posted yet.
	 */
	function getComments() {
		return $this->comments;
	}

	/**
	 * Creates displayable HTML-code from the comments using various template 
	 * files
	 *
	 * \param return if set to TRUE, the HTML-code will be returned as a String
	 * rather than printed out directly (optional)
	 * \return a string containing displayable HTML-code if the parameter return
	 * was set to true, otherwise nothing
	 */
	function render($return = FALSE) {
		if ($return) {
			// Catch everything that will be printed in the following code
			ob_start();
		}

		// Print heading
		$activityUrl = $this->getActivityUrl();
		include('tmpl_head.php');

		// Print comments

		// If the pagination is enabled, we insert a special HTML comment after
		// every N comments which is used by the JavaScript as an indicator
		// for a new page.
		echo '<!--pagebreak-->';

		foreach ($this->comments as $i => $comment) {
			$actorName = $comment->actor->displayName;
			$actorUrl = $comment->actor->url;
			$actorImage = preg_replace('/\?sz=.*/i', '', $comment->actor->image->url);
			$published = strtotime($comment->published);
			$comment = $comment->object->content;
			include('tmpl_comment.php');

			if (PAGINATION && (($i+1) % PAGINATION == 0)) {
				echo '<!--pagebreak-->';
			}
		}

		// An additional comment is written to the beginning and the end of
		// the actual comments in order to seperate the comments from header
		// and footer.
		echo '<!--pagebreak-->';

		// Delete the comment-specific fields
		unset($actor_name);
		unset($actor_url);
		unset($actor_image);
		unset($published);
		unset($comment);

		// Print footer
		include('tmpl_foot.php');

		if ($return) {
			// Return everything that has been printed since ob_start()
			return ob_get_clean();
		}
	}
}

// The following code serves the purpose to fetch the comments e.g. by
// using asynchronous requests. Just pass an activityId parameter when
// calling the PHP file and the file returns the comments for this
// specific ID.
if (isset($_REQUEST['activityId'])) {
	$pc = new PlusComments($_REQUEST['activityId']);
	$pc->render();
}