<?php if (isset($_POST['checkout_tag'])): ?>

<?php
	/*
		Catch AJAX post to checkout tag.
	*/
	$script = 'git checkout tags/' . $_POST['checkout_tag'] . ' 2>&1';
	system($script);
?>

<?php else: ?>

<!DOCTYPE html>
<html>
<head>
	<title>Tag Switcher GUI</title>
	<link rel="stylesheet" href="tagswitch.css" />
</head>
<body>
	<?php
		/*
			Sets up an array of the different tags in the git repo.

			Example:
			array(1) {
			  ["v1"]=>
			  array(7) {
			    ["tag_author"]=>
			    string(47) "Tagger: Way Spurr-Chen "
			    ["tag_date"]=>
			    string(38) "Date:   Sun Jan 19 18:50:57 2014 -0600"
			    ["tag_message"]=>
			    string(139) "First commit: green First commit: green First commit: green First commit: green First commit: green First commit: green First commit: green"
			    ["commit_author"]=>
			    string(47) "Author: Way Spurr-Chen "
			    ["commit_date"]=>
			    string(38) "Date:   Sun Jan 19 18:19:26 2014 -0600"
			    ["commit_hash"]=>
			    string(40) "5ccd5c7d052dfd9289062be257a91b4c4007c2bd"
			    ["commit_message"]=>
			    string(23) "    First commit: green"
			  }
			}
		*/

		// Get all of this git repo's tag labels
		$tag_labels = array();
		exec('git tag', $tag_labels);

		// We store the actual tag data in this
		$tag_objects = array();
		foreach ($tag_labels as $index=>$label) {
			// This is the temporary array that stores our results
			// from 'git show $label'
			$tag_detail_holder = array();
			exec("git show $label", $tag_detail_holder);

			$tag_objects[$label] = array();
			$tag_objects[$label]['tag_author'] = $tag_detail_holder[1];
			$tag_objects[$label]['tag_date'] = $tag_detail_holder[2];

			// Figure out tag message. We know it starts on 4, iterate until
			// hit the line starting with "commit", which we know will be when
			// the commit log starts

			// Line that the commit section of git show begins on
			$tag_message    = '';
			$commit_message = '';
			$commit_index   = null;
			for ($i = 4; $i < count($tag_detail_holder); ++$i) {
				// We know that that the string "commit" + SHA1 message will always be 47 chars long
				if (substr($tag_detail_holder[$i], 0, 6) == 'commit' &&
					strlen($tag_detail_holder[$i]) == 47) {
					$commit_index = $i;
					break;
				} else {
					$tag_message .= $tag_detail_holder[$i];
				}
			}

			$tag_objects[$label]['tag_message'] = $tag_message;
			$tag_objects[$label]['commit_author'] = $tag_detail_holder[$commit_index + 1];
			$tag_objects[$label]['commit_date'] = $tag_detail_holder[$commit_index + 2];
			$tag_objects[$label]['commit_hash'] = explode(' ', $tag_detail_holder[$commit_index])[1];

			// Do the same thing for commit message, but we check for when the diff message
			// shows up.
			for ($i = $commit_index + 4; $i < count($tag_detail_holder); ++$i) {
				if (substr($tag_detail_holder[$i], 0, 7) == 'diff --') {
					break;
				} else {
					$commit_message .= $tag_detail_holder[$i];
				}
			}

			$tag_objects[$label]['commit_message'] = $commit_message;
		}

		// Reverse to be in descending chronological order
		$tag_objects = array_reverse($tag_objects, true);
	?>

	<h1 class="page-title">Project Version Selector</h1>
	<div class="tags-container">
		<ul class="tags-list">
			<?php foreach ($tag_objects as $tag_label=>$tag): ?>
				<li data-tag-id="<?= $tag_label ?>" class="tags-list-item">
					<h2 class="tag-title"><?= $tag_label ?></h2>
					<p class="tag-message"><?= $tag['tag_message'] ?></p>
					<p class="tag-date"><?= $tag['tag_date'] ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="tags-info-container">
			<div class="tags-info active">
				<p>Select a tag from the left to view related information and activate it.</p>
			</div>
			<?php foreach ($tag_objects as $tag_label=>$tag): ?>
				<div data-tag-id="<?= $tag_label ?>" class="tags-info">
					<h2 class="tag-section-header">Tag Details</h2>
					<div class="tag-section-details">
						<h2 class="tag-section-title"><?= $tag_label ?></h2>
						<p class="tag-section-date"><?= $tag['tag_date'] ?></p>
						<p class="tag-section-message"><?= $tag['tag_message'] ?></p>
					</div>
					<h2 class="tag-section-header">Commit Details</h2>
					<div class="tag-section-details">
						<h2 class="tag-section-message"><?= $tag['commit_message'] ?></h2>
						<p class="tag-section-date"><?= $tag['commit_date'] ?></p>
					</div>
					<button data-tag-id="<?= $tag_label ?>" class="activate-tag">Activate Version</button>
					<p class="update-information"></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="tagswitch.js"></script>
</body>
</html>
<?php endif; ?>