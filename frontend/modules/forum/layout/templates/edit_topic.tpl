<div class="rightCol leftAlign">
	<section id="forumEdit" class="mod">
		<div class="inner">
			<h2>{$lblEditTopic|ucfirst}</h2>

			{form:edit}
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
					<input class="inputSubmit" type="submit" name="edit" value="{$lblSave|ucfirst}" />
				</p>
			{/form:edit}
		</div>
	</section>
</div>