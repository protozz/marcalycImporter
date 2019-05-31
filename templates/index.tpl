{strip}
{assign var="pageTitle" value="plugins.importexport.marcalycimporter.displayName"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#importExportTabs').pkpHandler('$.pkp.controllers.TabHandler');
		$('#importExportTabs').tabs('option', 'cache', true);
	{rdelim});
</script>
<div id="importExportTabs">
	<ul>
		<li><a href="#import-tab">{translate key="plugins.importexport.marcalycimporter.import"}</a></li>
		<li><a href="#exportSubmissions-tab">{translate key="plugins.importexport.marcalycimporter.exportSubmissions"}</a></li>
	</ul>
	<div id="import-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#importXmlForm').pkpHandler('$.pkp.controllers.form.FileUploadFormHandler',
					{ldelim}
						$uploader: $('#plupload'),
							uploaderOptions: {ldelim}
								uploadUrl: {plugin_url|json_encode path="uploadCompressedFile" escape=false},
								baseUrl: {$baseUrl|json_encode}
							{rdelim}
					{rdelim}
				);
			{rdelim});
		</script>
		<form id="importXmlForm" class="pkp_form" action="{plugin_url path="importCompressedFile"}" method="post">
			{csrf}
			{fbvFormArea id="importForm"}
				{* Container for uploaded file *}
				<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

				{fbvFormArea id="file"}
					{fbvFormSection title="plugins.importexport.marcalycimporter.import.instructions"}
						{include file="controllers/fileUploadContainer.tpl" id="plupload"}
					{/fbvFormSection}
				{/fbvFormArea}

				{fbvFormButtons submitText="plugins.importexport.marcalycimporter.import" hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
	<div id="exportSubmissions-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportSubmissionXmlForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportSubmissionXmlForm" class="pkp_form" action="{plugin_url path="exportSubmissions"}" method="post">
			{csrf}
			{fbvFormArea id="submissionsXmlForm"}
				{fbvFormSection}
					{assign var="uuid" value=""|uniqid|escape}
					<div id="export-submissions-list-handler-{$uuid}">
						<script type="text/javascript">
							pkp.registry.init('export-submissions-list-handler-{$uuid}', 'SelectSubmissionsListPanel', {$exportSubmissionsListData});
						</script>
					</div>
				{/fbvFormSection}
				{fbvFormButtons submitText="plugins.importexport.marcalycimporter.exportSubmissions" hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
