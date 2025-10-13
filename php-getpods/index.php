<p>Hello, world!</p>
<ul>
<li/>Hostname is <?= getenv('HOSTNAME') ?> (<?= $_ENV["HOSTNAME"] ?>)
<li/>Server name is <?= getenv('SERVER_NAME') ?> (<?= $_ENV["SERVER_NAME"] ?>)
<li/>Namespace is <?= getenv('NAMESPACE') ?> (<?= $_ENV["NAMESPACE"] ?>)
<li/>Service account name is <?= getenv('ACCOUNT_NAME') ?> (<?= $_ENV["ACCOUNT_NAME"] ?>)
<li/>Service name is <?= getenv('SERVICE_NAME') ?> (<?= $_ENV["SERVICE_NAME"] ?>)
<li/>Pod name is <?= getenv('POD_NAME') ?> (<?= $_ENV["POD_NAME"] ?>)
<li/>Pod IP is <?= getenv('POD_IP') ?> (<?= $_ENV["POD_IP"] ?>)
<li/>Application label is <code>app=<?= getenv('APP_LABEL') ?></code> (<?= $_ENV["APP_LABEL"] ?>)
</ul>
<?php
	$tfile = "/token/token";
	if (isset($_ENV['TOKEN_FILE'])) {
		$tfile = $_ENV['TOKEN_FILE'];
	}
	if (!file_exists($tfile)) {
	    print("<p>Can not read the token file. Exiting.</p>\n");
	    exit();
	}

	$token = file_get_contents($tfile);;
	print("<p>Using token <code>" . $token . "</code>.</p>\n");

	$errors = 0;
	if (!isset($_ENV["NAMESPACE"])) {
		print("<p>Namespace variable not set (NAMESPACE).</p>\n");
		$errors++;
	}
	if (!isset($_ENV["APP_LABEL"])) {
		print("<p>App label variable not set (APP_LABEL).</p>\n");
		$errors++;
	}
	if ($errors == 0) {
		$cs = curl_init("https://kubernetes.default/api/v1/namespaces/" . $_ENV["NAMESPACE"] . "/pods?labelSelector=app=" . $_ENV["APP_LABEL"]);
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cs, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cs, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer " . $token,
			"Accept: application/json"
			]);
		$response = curl_exec($cs);

		print("<p>Got pod list response:<p>\n");
?><code>
<?= $response ?>
</code>
<?php
	} else {
		print("<p>Skipping pod list due to errors.</p>\n");
	}
	$errors = 0;
	if (!isset($_ENV["NAMESPACE"])) {
		print("<p>Namespace variable not set (NAMESPACE).</p>\n");
		$errors++;
	}
	if (!isset($_ENV["SERVICE_NAME"])) {
		print("<p>Service name variable not set (SERVICE_NAME).</p>\n");
		$errors++;
	}
	if ($errors == 0) {
		$cs = curl_init("https://kubernetes.default/api/v1/namespaces/" . $_ENV["NAMESPACE"] . "/endpoints/" . $_ENV["SERVICE_NAME"]);
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cs, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cs, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer " . $token,
			"Accept: application/json"
			]);
		$response = curl_exec($cs);
		print("<p>Got endpoint list response:<p>\n");
?><code>
<?= $response ?>
</code>
<?php
		$dnsrec = dns_get_record($_ENV["SERVICE_NAME"] . "." . $_ENV["NAMESPACE"] . ".svc.cluster.local", DNS_A);
		if ($dnsrec) {
			print("<p>Got dns query response:<p>\n<code>\n");
			var_dump($dnsrec);
			print("\n</code>\n");
		} else {
			print("<p>Could not look up DNS record for service.</p>\n");
		}
	} else {
		print("<p>Skipping service test due to errors.</p>\n");
	}
?>
