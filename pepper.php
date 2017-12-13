<?php

/**
*
*	Pepper
*
*	Server file and directory permissions (re)setter written in PHP with Bootstrap Frontend UI
*
*	Sets/resets file and directory permissions.
*
*	Copyright [Lee Hodson](https://wpservicemasters.com)
*
*	Initial Release: Dec. 11, 2017
* This Release: Dec. 12, 2017
*
**/

/**
*
* LICENSE
*
*	GNU Affero General Public License v3.0
*
**/

/**
*
*	INSTRUCTIONS
*
*	Configure the options to
*
*		0) change this file's name to something unique e.g. from pepper.php to something-else.php.
*		1) set the password required to run the script (see OPTIONS below here).
*		2) set debug='1' to debug='0'.
*		3) visit domain.tld/pepper.php?pass=123 but change domain.tld to your domain name, pepper.php to your file name and 123 to your password.
*		4) Follow the onscreen instructions.
*
*/

/**
*
*	CAUTION
*
*	Pepper will do exactly what you tell it to do. There is no error checking. No undo. Only redo. Use wisely.
*
**/

# The HTML Page uses https://getbootstrap.com/docs/4.0/getting-started/introduction/

/**
*	OPTIONS
**/

#	Set the $secret key to something hard to guess.
$password = '123';
$debug = '1'; # 0 for OFF, 1 for ON

# INTERNAL

$producttitle = 'Pepper';
$productversion = '1.0.0';
$productdescription = 'Sets/resets file and directory permissions.';
$producttag = 'File and Directory permissions (re)setter';
$productauthor = 'Lee Hodson';
$productauthorsite = 'WP Service Masters';
$productauthorsiteurl = 'https://wpservicemasters.com/';
$authordonatelink = 'https://paypal.me/vr51/';

/**
*	GET POST
**/

# SECURITY

if ( $password != $_GET["pass"] ) {
    echo '<p>The password is wrong. Read program instruction in script file.</p>';
    die;
} else {

	# Set options

	$path = ( ! empty( $_POST['path'] ) ? $_POST['path'] : getcwd() );
	
	$directoryPerms = ( ! empty( $_POST['directoryPerms'] ) ? $_POST['directoryPerms'] : '0755' );
	$directoriesset = ( ! empty( $_POST['directoriesset'] ) ? $_POST['directoriesset'] : '0' );
	
	$filePerms = ( ! empty( $_POST['filePerms'] ) ? $_POST['filePerms'] : '0644' );
	$filesset = ( ! empty( $_POST['filesset'] ) ? $_POST['filesset'] : '0' );
	
	$excdir = ( ! empty( $_POST['excdir'] ) ? $_POST['excdir'] : '' );
		$excdirArray = explode( ',', $excdir );
	$excfile = ( ! empty( $_POST['excfile'] ) ? $_POST['excfile'] : '' );
		$excfileArray = explode( ',', $excfile );
	$excsubfiles = ( ! empty( $_POST['excsubfiles'] ) ? $_POST['excsubfiles'] : '' );
		$excsubfilesArray = explode( ',', $excsubfiles );
	# $del = ( ! empty( $_POST['del'] ) ? $_POST['del'] : '' ); # Not currently implemented -- risky
	
	$confirm = ( ! empty( $_POST['confirm'] ) ? $_POST['confirm'] : '0' );

	# Get URL
	$link = "$_SERVER[HTTP_HOST]";
	$thisScript = "$_SERVER[REQUEST_URI]";
	
}

/**
*	WORK
**/

if ( $confirm == '1' ) {

	header( 'Content-Type: application/json' );

	# Format directory ignore list.
	$ignoreDir = '';
	$bit = '-o ! -name';
	foreach ($excdirArray as $dir) {
	$ignoreDir .= "$bit '$dir' ";
	}
	$ignoreDir = substr( $ignoreDir, 3 );

	# Format file ignore list
	$ignoreFile = '';
	$bit = '-o ! -name';
	foreach ($excfileArray as $file) {
	$ignoreFile .= "$bit '$file' ";
	}
	$ignoreFile = substr( $ignoreFile, 3 );

	# Format directory (and files within ...) ignore list
	if ( !empty($excsubfilesArray) ) {
		$ignoreAll = '';
		foreach ($excsubfilesArray as $p) {
		$ignoreAll .= "-name '$p' -prune ";
		}
	} else {
		$ignoreAll = '';
	}
	$ignoreAll = ''; # Temp measure to prevent undefined variable warning. To remove when excsubfiles enabled.

	if ( $debug == '1' ) {

		$message = "<p>Changing permissions in path: $path</p><hr>";

		if ( $directoriesset == '0' ) {
			$message .= "<p>Directories changed to... $directoryPerms</p>";
		} else {
			$message .= "<p>Directory permissions not changed.</p>";
		}

		if ( $filesset == '0' ) {
			$message .= "<p>Files changed to: $filePerms</p>";
		} else {
			$message .= "<p>File permissions not changed.</p>";
		}
		$message .= "<p>Done in Debug Mode. No changes made.</p>"
		. "<p>Check server side to confirm changes completed successfully.</p>"
		. "<p>Check site <a href='//$link' target='_blank' rel='nofollow noopener'>$link</a> to confirm it still works.</p>";
		
		$message .="<pre>";
		ob_start();
			print_r($_POST);
			print_r($excdirArray);
			print_r($excfileArray);
			print_r($excsubfilesArray);
			$message .= ob_get_clean();
		$message .= "</pre>";
	
	} else {

		$message = "<p>Changing permissions in path: $path</p><hr>";
		
		if ( $directoriesset == '0' ) {
			$message .= "<p>Directories changed to... $directoryPerms</p>";
			exec ("find $path $ignoreDir $ignoreAll -type d -exec chmod $directoryPerms {} +");
		} else {
			$message .= "<p>Directory permissions not changed.</p>";
		}

		if ( $filesset == '0' ) {
			$message .= "<p>Files changed to: $filePerms</p>";
			exec ("find $path $ignoreFile $ignoreAll -type f -exec chmod $filePerms {} +");
		} else {
			$message .= "<p>File permissions not changed.</p>";
		}

		$message .= "<p>Done!</p>"
		. "<p>Check server side to confirm changes completed successfully.</p>"
		. "<p>Check site <a href='//$link' target='_blank' rel='nofollow noopener'>$link</a> to confirm it still works.</p>";

	}

	echo json_encode( array( 'status' => 0, 'html' => $message ) );
	
	die();
	
}


/**
*	HTML PAGE
**/

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<meta name="robots" content="noindex,nofollow,noodp"><!-- All Search Engines -->
<meta name="googlebot" content="noindex,nofollow"><!-- Google Specific -->

<title><?php echo "$producttitle $productversion - $producttag"; ?></title>
<meta name="description" content="<?php echo "$productdescription"; ?>">
<link rel="author" href="<?php echo "$productauthorsite"; ?>">

<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" ></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
<style>

.container {
	padding: 10px 40px;
	max-width: 940px;
}

#footer {

}

</style>

</head>
<body>

<div id="wrap">

	<header class="page-header bg-primary text-white">
		<div class="container">

			<h1><?php echo "$producttitle $productversion"; ?></h1>
			<p><?php echo "$producttag"; ?></p>

		</div>
	</header>

	<br>
	<section class="container">
		<div id="messageTop" class="alert alert-primary alert-dismissible fade show" role="alert">
			<div id="message"><?php if ( $debug='1' ) { echo '<h2>Debug Mode</h2><p class="text-muted">Disable debug in script file.</p>'; } else { echo '<h2>Live Mode</h2>'; } ?></div>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
	</section>

	<section class="page-content">
		<div class="container">
			<hr>
			<br>
			<h2>Settings</h2>
			<br>
			
			<form style="padding-bottom:10px;" id="processForm" role="form" method="post">
			
				<div id="path-field" class="form-group">
					<label class="control-label" for="path">Set permissions below the server path:</label>
					<input type="text" id="path" class="form-control text input-lg" name="path" value="<?php echo ( isset( $_POST['path'] ) ? $_POST['path'] : getcwd() ); ?>" required />
					<small class="form-text text-muted">Use the automatically detected path or set a path manually.</small>
					<small class="form-text text-muted">Example: </strong>/home/NAME/public_html/wp-content</small>
					<small class="form-text text-muted">Change path to whatever it needs to be. Do not set above /public_html.</small>
				</div>

				<div id="directoryPerms-field" class="form-group">
					<label class="control-label" for="directoryPerms">Permissions for directories:</label>
					<input type="text" id="directoryPerms" class="form-control text input-lg" name="directoryPerms" value="<?php echo ( isset( $_POST['directoryPerms'] ) ? $_POST['directoryPerms'] : '0755' ); ?>" minlength="4" maxlength="4" size="4" pattern="[0-7]{4}" required />
						<div class="form-check">
								<label for="directoriesset"><input type="checkbox" id="directoriesset" name="directoriesset" /> <span>Disable</span></label>
						</div>
					<small class="form-text text-muted">All directories will have their permissions reset to this value.</small>
					<small class="form-text text-muted">Default: </strong>0755</small>
					<small class="form-text text-muted">Always use 4 digits.</small>
				</div>

				<div id="filePerms-field" class="form-group">
					<label class="control-label" for="filePerms">Permissions for files:</label>
					<input type="text" id="filePerms" class="form-control text input-lg" name="filePerms" value="<?php echo ( isset( $_POST['filePerms'] ) ? $_POST['filePerms'] : '0644' ); ?>" minlength="4" maxlength="4" size="4" pattern="[0-7]{4}" required />
						<div class="form-check">
							<label for="filesset"><input type="checkbox" id="filesset" name="filesset" /> <span>Disable</span></label>
						</div>
					<small class="form-text text-muted">All files will have their permissions reset to this value.</small>
					<small class="form-text text-muted">Default: </strong>0644</small>
					<small class="form-text text-muted">Always use 4 digits.</small>
				</div>

				<div id="excdir-field" class="form-group">
					<label class="control-label" for="excdir">Directory names to ignore:</label>
					<input type="text" id="excdir" class="form-control text input-lg" name="excdir" value="<?php echo ( isset( $_POST['excdir'] ) ? $_POST['excdir'] : '*.*' ); ?>" />
					<small class="form-text text-muted">Comma separated list of directory names to skip. Files and directories below these will be processed. Simple regex values allowed.</small>
					<small class="form-text text-muted">Example: </strong>home,images,site.com</small>
					<small class="form-text text-muted">Value '*.*' skips directories that contain a dot but processes files and directories within them. These are usually addon or subdomain locations.</small>
				</div>

				<div id="excfile-field" class="form-group">
					<label class="control-label" for="excfile">File names to ignore:</label>
					<input type="text" id="excfile" class="form-control text input-lg" name="excfile" value="<?php echo ( isset( $_POST['excfile'] ) ? $_POST['excfile'] : '.*' ); ?>" />
					<small class="form-text text-muted">Comma separated list of file names to skip. Simple regex values allowed.</small>
					<small class="form-text text-muted">Example: </strong>index.php,.htaccess,readme.txt</small>
					<small class="form-text text-muted">Value '.*' skips dot files (files with a dot prefix). These are usually config or hidden files.</small>
				</div>

				<?php
				# Ignore for now. Inconsistent behaviour.
				/*
				<div id="excsubfiles-field" class="form-group">
					<label class="control-label" for="excsubfiles">Directory to ignore entirely:</label>
					<input type="text" id="excsubfiles" class="form-control text input-lg" name="excsubfiles" value="<?php <?php echo ( isset( $_POST['excsubfiles'] ) ? $_POST['excsubfiles'] : '*.com,*.co.uk' ); ?>" />
					<small class="form-text text-muted">Comma separated list of directories to skip completely. Simple regex values allowed.</small>
					<small class="form-text text-muted">Example: </strong>example.com,example.co.uk</small>
					<small class="form-text text-muted">The directory and all items below it will be skipped.</small>
				</div>
				*/
				?>
				
				<div class="form-check">
					<label class="form-check-label">
						<input class="form-check-input" type="checkbox" id="confirm" name="confirm" required>
						<span>Tick to confirm. Nothing will happen when you click Go! if you don't.</span>
					</label>
				</div>
				
				
				<button id="form-submit" class="btn btn-primary btn-lg btn-block" type="submit" action="<?php echo "$thisScript" ?>">Go!</button>

			</form>

		</div>
	</section>

	<section class="container">
		<div id="messageBottom" class="alert alert-primary alert-dismissible fade hidden" role="alert">
			<div id="message"></div>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
	</section>

	<section class="container">
		<div class="alert alert-info alert-dismissible fade show" role="alert">
			<p>Refresh this page with F5 or Ctrl+R to reload default settings.</p>
			<p>No need to refresh the page to run with new settings. Just amend and press Go!</p>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</section>

	<footer class="footer bg-dark text-white">
		<section class="container">
		
			<p class="text-secondary"><?php echo "$producttag"; ?> <a class="text-white" target="_blank" href="<?php echo "$productauthorsiteurl"; ?>"><?php echo "$productauthorsite"; ?></a></p>
			<hr>
			<p class="text-secondary">Did you find this tool useful? Help fun future development. <a class="text-white" href="<?php echo "$authordonatelink"; ?>" target="" rel="nofollow noopener">Donate through PayPal</a></p>
			
		</section>
	</footer>

</div>

<script type="text/javascript">

	jQuery(document).ready(function($) {
	
		// Disable/Enable inputs if required
		
		$('#directoriesset').click(function() {
		
				var $directoryPerms = $('#directoryPerms');
				var $directoriesset = $('label[for="directoriesset"] span');
				
				if ($directoryPerms.attr('disabled')) {
						$directoryPerms.removeAttr('disabled');
						$directoryPerms.attr('required', 'required');
						$directoriesset.text( 'Disable' );
				} else {
						$directoryPerms.removeAttr('required');
						$directoryPerms.attr('disabled', 'disabled');
						$directoriesset.text( 'Enable' );
				}
		});

		$('#filesset').click(function() {
		
				var $filePerms = $('#filePerms');
				var $filesset = $('label[for="filesset"] span');
				if ($filePerms.attr('disabled')) {
						$filePerms.removeAttr('disabled');
						$filePerms.attr('required', 'required');
						$filesset.text( 'Disable' );
				} else {
						$filePerms.removeAttr('required');
						$filePerms.attr('disabled', 'disabled');
						$filesset.text( 'Enable' );
				}
		});

		// Process the form
		$('#processForm').submit(function(e) {
		// console.log(e);

			e.preventDefault();
			$('#form-submit').attr( 'disabled', 'disabled' );
			$('#form-submit').html( 'Working ...' );

			// Initiate Variables With Form Content

			var path = $("#path").val();
			var directoryPerms = $("#directoryPerms").val();
			var directoriesset = $("#directoriesset").val();
			var filePerms = $("#filePerms").val();
			var filesset = $("#filesset").val();
			var excdir = $("#excdir").val();
			var excfile = $("#excfile").val();
			var excsubfiles = $("#excsubfiles").val();
			var del = $("#del").val();
			var confirm = $("#confirm").val();
			
			var postdata = {
				path: path,
				directoryPerms: directoryPerms,
				directoriesset: directoriesset,
				filePerms: filePerms,
				filesset: filesset,
				excdir: excdir,
				excfile: excfile,
				// del: del,
				confirm: confirm
			};

			// Set checkbox values
			if ( $('#directoriesset').is(':checked') ) {
				$.extend( postdata, { directoriesset: 1 } );
			} else {
				$.extend( postdata, { directoriesset: 0 } );
			}

			if ( $('#filesset').is(':checked') ) {
				$.extend( postdata, { filesset: 1 } );
			} else {
				$.extend( postdata, { filesset: 0 } );
			}

			if ( $('#confirm').is(':checked') ) {
				$.extend( postdata, { confirm: 1 } );
			} else {
				$.extend( postdata, { confirm: 0 } );
			}

			// Process returned data
			$.post( '', postdata, function( data ) {
			// console.log(postdata);

				if ( 0 == data.status ) {
					$('#field1').removeClass( 'has-error' );
				} else {
					$('#field1').addClass( 'has-error' );
				}
				$('#messageBottom #message').html( data.html );
				$('#messageBottom').removeClass( "hidden" );
				$('#messageBottom').addClass( "show" );
				$('#form-submit').removeAttr( 'disabled' );
				$('#form-submit').html( 'Go!' );

			}, 'json');

		});
	});
	
</script>

</body>
</html>
