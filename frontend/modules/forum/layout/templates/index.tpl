{*
	variables that are available:
	- {items}: contains all topics
*}


{option:!items}
	<div id="forumIndex">
		<section class="mod">
			<div class="inner">
				<div class="bd content">
					<p>{$msgForumNoItems}</p>
					<p><a href="{$var|geturlforblock:'forum':'add_topic'}" title="{$lblNewTopic}">{$lblNewTopic|ucfirst}</a></p>
				</div>
			</div>
		</section>
	</div>
{/option:!items}

{option:items}
	<section id="forumIndex" class="mod">
		<div class="inner">
			<h2 class="alignLeft">{$lblTopics|ucfirst}</h2>
			<a class="button alignRight" href="{$var|geturlforblock:'forum':'add_topic'}" title="{$lblNewTopic}">{$lblNewTopic|ucfirst}</a>
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
	</section>
{/option:items}
