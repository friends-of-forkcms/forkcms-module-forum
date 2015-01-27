{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblForum|ucfirst}: {$lblEditPost}</h2>
	<div class="buttonHolderRight">
		<a href="{$detailUrl}" class="button icon iconZoom previewButton targetBlank">
			<span>{$lblView|ucfirst}</span>
		</a>
	</div>
</div>

{form:edit}
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabVersions">{$lblVersions|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table width="100%">
				<tr>
					<td id="leftColumn">

						{* Main content *}
						<div class="box">
							<div class="heading">
								<h3>
									<label for="text">{$lblMainContent|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
								</h3>
							</div>
							<div class="options">
								{$txtText} {$txtTextError}
							</div>
						</div>

						{* Extra *}
						<div class="box">
							<div class="heading">
								<div class="oneLiner">
									<h3>
										<label>{$lblExtra|ucfirst}</label>
									</h3>
								</div>
							</div>
							<div class="horizontal">
								<div class="options">
									<p>
										<label for="profile_id">{$lblProfileId|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
										{$txtProfileId} {$txtProfileIdError}
									</p>
								</div>
							</div>
						</div>

					</td>

					<td id="sidebar">
						<div id="publishOptions" class="box">
							<div class="heading">
								<h3>{$lblStatus|ucfirst}</h3>
							</div>

							<div class="options">
								<ul class="inputList">
									{iteration:type}
									<li>
										{$type.rbtType}
										<label for="{$type.id}">{$type.label}</label>
									</li>
									{/iteration:type}
								</ul>
							</div>
						</div>

					</td>
				</tr>
			</table>
		</div>

		<div id="tabVersions">
			<div class="tableHeading">
				<div class="oneLiner">
					<h3 class="oneLinerElement">{$lblPreviousVersions|ucfirst}</h3>
					<abbr class="help">(?)</abbr>
					<div class="tooltip" style="display: none;">
						<p>{$msgHelpRevisions}</p>
					</div>
				</div>
			</div>

			{option:revisions}
			<div class="dataGridHolder">
				{$revisions}
			</div>
			{/option:revisions}

			{option:!revisions}
				<p>{$msgNoRevisions}</p>
			{/option:!revisions}
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}