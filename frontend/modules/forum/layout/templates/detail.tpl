{*
	variables that are available:
	- {$topic}: contains data about the topic and the posts
*}

<article id="forumDetail" class="mod topic">
	<div id="originalPost">

		{* Right col - First post *}
		<div class="rightCol leftAlign">
			<header class="hd">
				<h1>{$item.title|ucfirst}</h1>
			</header>
			<div class="bd content">
				{$item.text}
			</div>
		</div>

		{* Left col - Topic start profile information *}
		<aside class="leftCol">
			<div class="author">
				<div class="imageHolder">
					<a href="{$var|geturlforblock:'profiles':'detail'}/{$item.profile.url}" title="Profile of {$item.profile.display_name}">
						{option:!item.profile.avatar}
							<img src="{$FRONTEND_CORE_URL}/layout/images/default_author_avatar.gif" width="48" height="48" alt="{$item.profile.display_name}" class="replaceWithGravatar" data-gravatar-id="{$item.profile.gravatar_id}" />
						{/option:!item.profile.avatar}
						{option:item.profile.avatar}
							<img src="{$FRONTEND_FILES_URL}/profiles/avatars/48x48/{$item.profile.avatar}" alt="{$item.profile.display_name}" />
						{/option:item.profile.avatar}
					</a>
				</div>
				<div class="profileContent">
					<h3><a href="{$var|geturlforblock:'profiles':'detail'}/{$item.profile.url}" title="Profile of {$item.profile.display_name}">{$item.profile.display_name}</a></h3>
					<p>{$item.created_on|timeago}</p>
				</div>
				{option:item.is_author}
					<div class="postActions">
						<p><a href="{$var|geturlforblock:'forum':'edit_topic'}?id={$item.id}">{$lblEdit|ucfirst}</a></p>
					</div>
				{/option:item.is_author}
			</div>
		</aside>

		<div class="clearfix"></div>

	</div>

	{option:item.posts}
		<div class="replies">
			{iteration:item.posts}
				<article id="post-{$item.posts.id}" class="mod post">
					<div class="rightCol leftAlign">
						<div class="bd content">
							{$item.posts.text}
						</div>
					</div>
					<aside class="leftCol">
						<div class="author">
							<div class="imageHolder">
								<a href="{$var|geturlforblock:'profiles':'detail'}/{$item.posts.profile.url}" title="Profile of {$item.posts.profile.display_name}">
									{option:!item.posts.profile.avatar}
										<img src="{$FRONTEND_CORE_URL}/layout/images/default_author_avatar.gif" width="48" height="48" alt="{$item.pposts.rofile.display_name}" class="replaceWithGravatar" data-gravatar-id="{$item.posts.profile.gravatar_id}" />
									{/option:!item.posts.profile.avatar}
									{option:item.posts.profile.avatar}
										<img src="{$FRONTEND_FILES_URL}/profiles/avatars/48x48/{$item.posts.profile.avatar}" alt="{$item.posts.profile.display_name}" />
									{/option:item.posts.profile.avatar}
								</a>
							</div>
							<div class="profileContent">
								<h3><a href="{$var|geturlforblock:'profiles':'detail'}/{$item.posts.profile.url}" title="Profile of {$item.posts.profile.display_name}">{$item.posts.profile.display_name}</a></h3>
								<p>{$item.posts.created_on|timeago}</p>
							</div>
							{option:item.posts.is_author}
								<div class="postActions">
									<p><a href="{$var|geturlforblock:'forum':'edit_post'}?id={$item.posts.id}">{$lblEdit|ucfirst}</a></p>
								</div>
							{/option:item.posts.is_author}
						</div>
					</aside>
				</article>
			{/iteration:item.posts}
		</div>
	{/option:item.posts}
</article>

<div class="rightCol leftAlign">
	<section id="forumPostForm" class="mod">
		<div class="inner">
			<header class="hd">
				<h3 id="{$actPost}">{$msgComment|ucfirst}</h3>
			</header>
			<div class="bd">
				{option:commentIsSpam}<div class="message error"><p>{$msgBlogCommentIsSpam}</p></div>{/option:commentIsSpam}
				{option:commentIsAdded}<div class="message success"><p>{$msgBlogCommentIsAdded}</p></div>{/option:commentIsAdded}
				{form:addPostForm}
					<p class="maxInput{option:txtTextError} errorArea{/option:txtTextError}">
						<label for="message">{$lblMessage|ucfirst}</label>
						{$txtText} {$txtTextError}
						<span class="smallText">{$msgMarkdownHelp}</span>
					</p>
					<p>
						<input class="inputSubmit" type="submit" name="comment" value="{$lblComment|ucfirst}" />
					</p>
				{/form:addPostForm}
				{option:!isLoggedIn}
					<p>{$msgLoginToReply|sprintf:{$var|geturlforblock:'profiles':'login'}:{$var|geturlforblock:'profiles':'register'}}</p>
				{/option:!isLoggedIn}
			</div>
		</div>
	</section>
</div>