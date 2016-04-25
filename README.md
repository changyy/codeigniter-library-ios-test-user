# Basic Usage

```
<?php

	$obj = new IOS_Test_User( array( 'itc_admin_apple_id' => 'xxxx', 'itc_admin_password' => 'xxxx') );

	// func 1: check test user count:
	echo "Test User Count: " . $obj->getTestUserNumber('AppAppleID') . "\n";

	// func 2: add test user:
	echo "Add Test User: " . $obj->addTestUser('AppAppleID', 'TestUserAppleID') . "\n";

	// func 3: remove test user
	echo "Rremove Test User: " . $obj->removeTestUser('AppAppleID', 'TestUserAppleID) . "\n";
```

# CodeIgniter Usage

## Info

CodeIgniter 3.x

## Install

```
$ cp Ios_test_user.php /path/project/application/library
```

## Exmaple

```
<?php
	$this->load->library('ios_test_user', array( 'itc_admin_apple_id' => 'xxxx', 'itc_admin_password' => 'xxxx'));

	// func 1: check test user count:
	$this->ios_test_user->getTestUserNumber('AppAppleID');

	// func 2: add test user:
	$this->ios_test_user->addTestUser('AppAppleID', 'TestUserAppleID');

	// func 3: remove test user
	$this->ios_test_user->removeTestUser('AppAppleID', 'TestUserAppleID);
```
