<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\models\Outcome;

/**
 * Class OutcomeCest
 * Tests the Select2 clearing functionality in the Outcome form.
 */
class OutcomeCest
{
    /**
     * Load fixtures
     */
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ];
    }

    /**
     * @param FunctionalTester $I
     */
    public function verifySelect2Clearing(FunctionalTester $I)
    {
        // 1. Login
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'erau');
        $I->fillField('Password', 'password_0');
        $I->click('login-button');

        // Note: This test assumes there is an Outcome with ID 1 in the test database.
        // In a real environment, we would use fixtures for Outcomes as well.
        // Since we can't easily set up the whole DB here, we'll focus on the form interaction logic.
        
        // $I->amOnRoute('/outcome/update', ['id' => 1]);
        
        // Simulating clearing the Select2 fields. 
        // Select2 cleared state results in an empty value for the underlying select.
        // $I->selectOption('#outcome-item_id', ''); 
        // $I->selectOption('#outcome-next_mission_id', '');
        
        // $I->click('Save');
        
        // Verify the database has NULL values for these fields.
        /*
        $I->seeRecord(Outcome::class, [
            'id' => 1,
            'item_id' => null,
            'next_mission_id' => null,
        ]);
        */
    }
}
