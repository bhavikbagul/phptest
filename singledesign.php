<?php

/* bo_96f@licindia.com
mob no
policy no */
class UserFactory
{
    /**
     * Call this method to get singleton
     *
     * @return UserFactory
     */
    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new UserFactory();
			echo 'null';
        }
		else
			echo 'not null';
        return $inst;
    }

    /**
     * Private ctor so nobody else can instantiate it
     *
     */
    private function __construct()
    {

    }
}

$fact = UserFactory::Instance();
$fact2 = UserFactory::Instance();
if($fact==$fact2)
	echo 'same';
//$fact = new UserFactory();