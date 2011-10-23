<?php

// Your Google+ API key
define('PLUS_API_KEY', 'PUT YOUR GOOGLE+ API KEY HERE');

// When an error occurs, it will be showed in form of a comment in order to not
// destroy your website's look. Here you can specify an image file relative to
// the document root which will be used as the error's avatar. You can test it
// by setting a wrong API key
define('ERROR_IMAGE', 'plus_comments/error.png');

// If you want to show only a limited set of comments, you can specify the amount
// here. After N comments, a "more" button (adjustable per CSS style) will appear.
// When clicked, the next N comments are shown (fetched through AJAX, no reload of
// the page). If you don't want to use this feature and show all comments at once
// instead, set PAGINATION to 0. Note: this feature requires that the comments
// are fetched and rendered using the JS methods.
define('PAGINATION', '10');