<div class="rightCol leftAlign">
	<section id="forumAddTopic" class="mod">
		<div class="inner">
			<h2>{$lblNewTopic|ucfirst}</h2>

			{form:addTopicForm}
				<p class="maxInput{option:txtTitleError} errorArea{/option:txtTitleError}">
					<label for="title">{$lblTitle|ucfirst}</label>
					{$txtTitle} {$txtTitleError}
				</p>
				<p class="maxInput{option:txtTextError} errorArea{/option:txtTextError}">
					<label for="text">{$lblMessage|ucfirst}</label>
					{$txtText} {$txtTextError}
					<span class="smallText">{$msgMarkdownHelp}</span>
				</p>
				<p>
					<input class="inputSubmit" type="submit" name="add" value="{$lblAdd|ucfirst}" />
				</p>
			{/form:addTopicForm}
		</div>
	</section>
</div>