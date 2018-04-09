<h1>QuickBooks</h1>
<ul class="paginate-tables">
	<li class="tab-link active" data-target="quickbooks-invoices-table">Invoices</li>
	<li class="tab-link" data-target="quickbooks-estimates-table">Estimates</li>
</ul>
<div class="tab-content" data-target="quickbooks-invoices-table">
	<table class="table table-striped table-bordered" style="width:100%">
	    <thead>
	        <tr>
	            <th style="text-align: center;">Invoice</th>
	            <th style="text-align: center;">Txn Date</th>
	            <th style="text-align: center;">Due Date</th>
	            <th style="text-align: center;">Total</th>
	            <th style="text-align: center;">Balance</th>
	        </tr>
	    </thead>
	    <tbody>
	    	<?php 
				if($invoices) {
					foreach($invoices as $invoice) {
						$inv = $this->object_to_array($invoice);
						?>
						<tr class="one-invoice">
				            <td>
				            	<a href="https://<?= $qb_url ?>/app/invoice?txnId=<?= $inv['Id'] ?>" target="_blank"><?= $inv['DocNumber'] ?></a>
				            </td>
				            <td><?= $inv['TxnDate'] ?></td>
				            <td><?= $inv['DueDate'] ?></td>
				            <td><?= $inv['CurrencyRef'] ?> <?= $inv['TotalAmt'] ?></td>
				            <td><?= $inv['CurrencyRef'] ?> <?= $inv['Balance'] ?></td>
				        </tr>	
						<?php
					}
				} else {
			?> 		<tr style="background-color: #d1d1d1;" class="empty-table">
			        	<td colspan="6" style="text-align: center;">No Invoices.</td>
		        	</tr>
	    	<?php
				}
	        ?>
	    </tbody>
	</table>
</div>
<div class="tab-content" data-target="quickbooks-estimates-table" style="display: none;">
	<table class="table table-striped table-bordered" style="width:100%">
	    <thead>
	        <tr>
	            <th style="text-align: center;">Estimate</th>
	            <th style="text-align: center;">Txn Date</th>
	            <th style="text-align: center;">Due Date</th>
	            <th style="text-align: center;">Total</th>
	            <th style="text-align: center;">Balance</th>
	        </tr>
	    </thead>
	    <tbody>
	    	<?php 
				if($estimates) {
					foreach($estimates as $estimate) {
						$est = $this->object_to_array($estimate);
						?>
						<tr class="one-estimate">
				            <td>
				            	<a href="https://<?= $qb_url ?>/app/estimate?txnId=<?= $est['Id'] ?>" target="_blank"><?= $est['DocNumber'] ?></a>
			            	</td>
				            <td><?= $est['TxnDate'] ?></td>
				            <td><?= isset($est['DueDate']) ? $est['DueDate'] : '- not set -' ?></td>
				            <td><?= $est['CurrencyRef'] ?> <?= $est['TotalAmt'] ?></td>
				            <td><?= $est['CurrencyRef'] ?> <?= isset($est['Balance']) ? $est['Balance'] : '0.00' ?></td>
				        </tr>	
						<?php
					}
				} else {
			?> 		<tr style="background-color: #d1d1d1;" class="empty-table">
			        	<td colspan="6" style="text-align: center;">No Estimates.</td>
		        	</tr>
	    	<?php
				}
	        ?>
	    </tbody>
	</table>
</div>