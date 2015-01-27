{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblForum|ucfirst}</h2>
</div>

<div id="tabs" class="tabs">
	<ul>
		<li><a href="#tabPublished">{$lblPublished|ucfirst} ({$numPublished})</a></li>
		<li><a href="#tabSpam">{$lblSpam|ucfirst} ({$numSpam})</a></li>
	</ul>

	<div id="tabPublished">
		{option:dgPublished}
			<form action="{$var|geturl:'mass_action'}" method="get" class="forkForms" id="itemsPublished">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="visible" />
					{$dgPublished}
				</div>
			</form>
		{/option:dgPublished}
		{option:!dgPublished}{$msgNoPostsOrTopics}{/option:!dgPublished}
	</div>

	<div id="tabSpam">
		{option:dgSpam}
			<form action="{$var|geturl:'mass_action'}" method="get" class="forkForms" id="itemsSpam">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="spam" />
					<div class="generalMessage infoMessage">
						{$msgDeleteAllSpam}
						<a href="{$var|geturl:'delete_spam'}">{$lblDelete|ucfirst}</a>
					</div>
					{$dgSpam}
				</div>
			</form>
		{/option:dgSpam}
		{option:!dgSpam}{$msgNoSpam}{/option:!dgSpam}
	</div>
</div>

<div id="confirmDeletePublished" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>
<div id="confirmSpamPublished" title="{$lblSpam|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassSpam}</p>
</div>
<div id="confirmDeleteSpam" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}