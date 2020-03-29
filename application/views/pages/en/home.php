<br />
<br />
<br />
<h1>Application Leave System</h1>

<p>Welcome to APLeaveSys created by Karl Joseph Saycon,MSIT for companies who wants to digitized the way we do file our leaves. No worries it's paperless!</p>
<p>Feel free to click on the online help icon for each screen (<i class="mdi mdi-help-circle-outline"></i>).
  This will give you access to the documentation of the feature you are using.</p>
<p>if you are an employee, you could now:</p>
<ul>
    <li>See your <a href="<?php echo base_url();?>leaves/counters">leave balance</a>.</li>
    <li>See the <a href="<?php echo base_url();?>leaves">list of the leave requests you have submitted</a>.</li>
    <li>Request a <a href="<?php echo base_url();?>leaves/create">new leave</a>.</li>
</ul>

<br />

<p>If you are the line manager of other employee(s), you could now:</p>
<ul>
    <li>Validate <a href="<?php echo base_url();?>requests">leave requests submitted to you</a>.</li>
    <?php if ($this->config->item('disable_overtime') == FALSE) { ?>
    <li>Validate <a href="<?php echo base_url();?>overtime">overtime requests submitted to you</a>.</li>
    <?php } ?>
</ul>
