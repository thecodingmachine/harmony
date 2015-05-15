<?php
use Mouf\Installer\AbstractInstallTask;

/* @var $this Mouf\Controllers\InstallController */
?>
<script type="text/javascript">
$(document).ready(function() {
	$("table.table button").click(function() {
		return confirm("Are you sure you want to perform this action? It is usually wiser to use the 'Run all install processes' button at the top of the screen.");
	});
});
</script>

<h1>Install tasks</h1>

<?php if ($this->installs) {
    if ($this->countNbTodo) {
        ?>

<form action="install" method="post">
	<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />
	<button class="btn btn-success btn-lg"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> Run the <?php echo $this->countNbTodo ?> pending install task<?php echo($this->countNbTodo == 1 ? "" : "s") ?></button>
</form>
<br/>

<?php

    } else {
        ?>
<div class="alert alert-success">All install tasks have been executed.</div>
<?php

    }
    ?>

<table class="table table-striped">
	<tr>
		<th style="width: 30%">Package</th>
		<th style="width: 40%">Description</th>
		<th style="width: 15%">Status</th>
		<th style="width: 15%">Action</th>
	</tr>
	<?php foreach ($this->installs as $installTask):
        /* @var $installTask AbstractInstallTask */
    ?>
	<tr>
		<td><?php echo plainstring_to_htmlprotected($installTask->getPackage()->getName());
    ?></td>
		<td><?php echo plainstring_to_htmlprotected($installTask->getDescription());
    ?></td>
		<td><?php
        if ($installTask->getStatus() == AbstractInstallTask::STATUS_TODO) {
            echo '<span class="glyphicon glyphicon-time" aria-hidden="true"></span> Awaiting installation';
        } elseif ($installTask->getStatus() == AbstractInstallTask::STATUS_DONE) {
            echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Done';
        } else {
            echo plainstring_to_htmlprotected($installTask->getStatus());
        }
    ?>
		</td>
		<td>
		<form action="install" method="post" style="margin: 0px">
		<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />
		<input type="hidden" name="task" value="<?php echo plainstring_to_htmlprotected(serialize($installTask->toArray()));
    ?>">

		<?php
        if ($installTask->getStatus() == "todo") {
            ?>
			<button class="btn btn-info"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> Manual install</button>
		<?php

        } else {
            ?>
			<button class="btn btn-danger"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> Reinstall</button>
		<?php

        }
    ?>
		</form>
		</td>
	</tr>
	<?php endforeach;
    ?>
</table>

<?php

} else {
    ?>
	<div class="alert alert-success">No installed packages have install processes</div>
<?php

} ?>
