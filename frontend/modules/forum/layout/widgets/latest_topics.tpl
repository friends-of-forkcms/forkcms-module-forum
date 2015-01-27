{*
	variables that are available:
	- {$items}:
*}

<section id="forumIndex" class="mod">

	{option:!items}
		<div class="inner">
			<div class="bd content">
				<p>{$msgForumNoItems}</p>
			</div>
		</div>
	{/option:!items}

	{option:items}
		<div class="inner">
			<header class="hd">
				<h2 class="alignLeft">{$lblForum|ucfirst}</h2>
				<a class="button alignRight" href="{$var|geturlforblock:'forum':'add_topic'}" title="{$lblNewTopic}">{$lblNewTopic|ucfirst}</a>
			</header>
			<div class="bd">
				<table>
					<thead>
						<th class="title">{$lblTitle|ucfirst}</th>
						<th class="numPosts">{$lblReplies|ucfirst}</th>
						<th class="lastPost">{$lblLastPost|ucfirst}</th>
					</thead>
					{iteration:items}
						<tr>
							<td class="title"><a href="{$var|geturlforblock:'forum':'detail'}/{$items.url}" title="{$items.title}">{$items.title|ucfirst}</a></td>
							<td class="numPosts">{$items.num_posts}</td>
							<td class="lastPost">
								{$items.last_post_date|timeago}
								<br />{$lblBy} 
								{$items.last_post_author.display_name}
							</td>
						</tr>
					{/iteration:items}
				</table>
			</div>
		</div>
		<footer class="ft lineMiddleButton">
			<p><a class="button" href="{$var|geturlforblock:'forum'}" title="{$lblGoToForum|ucfirst}">{$lblGoToForum|ucfirst}</a></p>
		</footer>
	{/option:items}

</section>
