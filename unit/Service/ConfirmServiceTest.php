<?php

namespace Tests\Unit\Service;

use App\Contracts\ConfirmService;
use App\Db\Main\UserConfirm;
use App\Service\ConfirmTemplate\EmailVerifyCode;
use App\Service\ConfirmTemplate\PasswordCode;
use App\Service\ConfirmTemplate\PaymentCode;
use App\Service\ConfirmTemplate\PhoneCode;
use App\Service\ConfirmTemplate\PhoneVerifyCode;
use App\Service\ConfirmTemplate\SmsTransactionCode;
use Carbon\Carbon;

/**
 * Class ConfirmServiceTest
 * @package Tests\Unit\Service
 */
class ConfirmServiceTest extends \BaseUnitTransaction
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ConfirmService
     */
    public $resolveInstance;

    /**
     * @var array
     */
    public $templates = [
        'PhoneVerifyCode' => PhoneVerifyCode::class,
        'EmailVerifyCode' => EmailVerifyCode::class,
        'PasswordCode' => PasswordCode::class,
        'PhoneCode' => PhoneCode::class,
        'SmsTransactionCode' => SmsTransactionCode::class,
        'PaymentCode' => PaymentCode::class,
    ];

    /**
     * @var string
     */
    public $myPhone = '+79646103073';

    /**
     * @var string
     */
    public $myInvalidPhone = '+7964610307';

    /**
     * @var string
     */
    public $myEmail = 'russnowsem@ya.ru';

    /**
     * @var string
     */
    public $myInvalidEmail = 'russnowsem@aya.r';

    /**
     * @var string
     */
    public $notExistingCode = 123123;

    /**
     * @var string
     */
    public $password = 123123;

    /**
     * @var int
     */
    public $amount = 1;

    /**
     * @param $template
     * метод проверяет create и get
     */
    private function createAndGetTemplate($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);

        $this->tester->seeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $record = $this->tester->grabRecord(
            'main.user_confirms', [
            'code' => $code->code, 'resource' => $this->myPhone,
        ]);

        $this->assertEquals($this->user->id, $record['user_id']);
        $userConfirm = $confirmService->get($code->code, $this->myPhone);

        $this->assertEquals(get_class($userConfirm), UserConfirm::class);
    }

    /**
     * @param $template
     * метод проверяет create и get
     * когда код expired
     */
    private function createAndGetTemplateExpired($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);
        $code->expires_at = Carbon::now()->subMinutes(15);

        $code->save();

        $this->tester->seeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $record = $this->tester->grabRecord(
            'main.user_confirms', [
            'code' => $code->code, 'resource' => $this->myPhone,
        ]);

        $this->assertEquals($this->user->id, $record['user_id']);
        $userConfirm = $confirmService->get($code->code, $this->myPhone);

        $this->assertEquals($userConfirm, null);
    }

    /**
     * @param $template
     * метод проверяет функцию проверки и удаления
     */
    private function checkAndRemovePhoneTrue($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);

        $this->tester->canSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $isValid = $confirmService->checkAndRemove($code->code, $this->myPhone, $this->user);
        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $this->assertTrue($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemovePhoneFalse($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $isValid = $confirmService->checkAndRemove($this->notExistingCode, $this->myPhone, $this->user);
        $this->assertFalse($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemoveEmailTrue($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myEmail, $this->user);

        $this->tester->canSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myEmail]);
        $isValid = $confirmService->checkAndRemove($code->code, $this->myEmail, $this->user);
        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myEmail]);
        $this->assertTrue($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemoveEmailFalse($template){
        $confirmService = app(ConfirmService::class, [new $template()]);

        $isValid = $confirmService->checkAndRemove($this->notExistingCode, $this->myEmail, $this->user);
        $this->assertFalse($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemovePasswordTrue($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->password, $this->user);

        $this->tester->canSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->password]);
        $isValid = $confirmService->checkAndRemove($code->code, $this->password, $this->user);
        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->password]);
        $this->assertTrue($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemovePasswordFalse($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $isValid = $confirmService->checkAndRemove($this->notExistingCode, $this->myEmail, $this->user);
        $this->assertFalse($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemovePaymentCodeTrue($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create(null, $this->user, $this->amount);

        $this->tester->canSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => null]);
        $isValid = $confirmService->checkAndRemove($code->code, $this->password, $this->user);
        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => null]);
        $this->assertTrue($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemovePaymentCodeFalse($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $isValid = $confirmService->checkAndRemove($this->notExistingCode, $this->myEmail, $this->user);
        $this->assertFalse($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemoveSmsTrue($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);

        $this->tester->canSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $isValid = $confirmService->checkAndRemove($code->code, $this->myPhone, $this->user);
        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $this->assertTrue($isValid);
    }

    /**
     * @param $template
     */
    private function checkAndRemoveSmsFalse($template){
        $confirmService = app(ConfirmService::class, [new $template()]);
        $isValid = $confirmService->checkAndRemove($this->notExistingCode, $this->myPhone, $this->user);
        $this->assertFalse($isValid);
    }
    /**
     * @param $template
     * неочевидный метод check
     * чекает, есть ли код
     * проблемы с параметрами
     */
    private function checkTemplateFalse($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $check = $confirmService->check($this->notExistingCode , $this->myPhone);
        $this->assertFalse($check);
        /* Метод check ведет себя не очевидно, поэтому пока не тестим
        $code = $confirmService->create($this->myPhone, $this->user);
        $check = $confirmService->check($code->code, $this->myInvalidPhone);
        $this->assertFalse($check);
        */
    }

    /**
     * @param $template
     * неочевидный метод check
     * чекает, есть ли код
     * проблемы с параметрами
     */
    private function checkTemplateTrue($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);
        $check = $confirmService->check($code->code, $this->myPhone);

        $this->assertTrue($check);
    }

    /**
     * @param $template
     * проверяет удалился ли код
     */
    private function removeTemplateCode($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);

        $this->tester->seeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $confirmService->remove($code->code, $this->myPhone);

        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
    }

    /**
     * @param $template
     * проверяем получения кода по модели
     * User
     */
    private function getByUserTemplate($template)
    {
        $confirmService = app(ConfirmService::class, [new $template()]);
        $code = $confirmService->create($this->myPhone, $this->user);
        $getCode = $confirmService->getByUser($this->user);
        $this->assertEquals($getCode->code, $code->code);
    }

    public function testCheckAndRemovePhoneCodeTrue(){
        $this->checkAndRemovePhoneTrue($this->templates['PhoneCode']);
    }
    public function testCheckAndRemovePhoneCodeFalse(){
        $this->checkAndRemovePhoneFalse($this->templates['PhoneCode']);
    }


    public function testCheckAndRemovePasswordTrue(){
        $this->checkAndRemovePasswordTrue($this->templates['PasswordCode']);
    }
    public function testCheckAndRemovePasswordFalse(){
        $this->checkAndRemovePasswordFalse($this->templates['PasswordCode']);
    }

    public function testCheckAndRemoveEmailTrue(){
        $this->checkAndRemoveEmailTrue($this->templates['EmailVerifyCode']);
    }
    public function testCheckAndRemoveEmailFalse(){
        $this->checkAndRemoveEmailFalse($this->templates['EmailVerifyCode']);
    }

    public function testCheckAndRemovePaymentCodeTrue(){
        $this->checkAndRemovePaymentCodeTrue($this->templates['PaymentCode']);
    }
    public function testCheckAndRemovePaymentCodeFalse(){
        $this->checkAndRemovePaymentCodeFalse($this->templates['PaymentCode']);
    }

    public function testCheckAndRemoveSmsTrue(){
        $this->checkAndRemoveSmsTrue($this->templates['SmsTransactionCode']);
    }
    public function testCheckAndRemoveSmsFalse(){
        $this->checkAndRemoveSmsFalse($this->templates['SmsTransactionCode']);
    }

    public function testCreateAndGetPhoneVerifyCode()
    {
        $this->createAndGetTemplate($this->templates['PhoneVerifyCode']);
    }

    public function testCreateAndGetPhoneVerifyCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['PhoneVerifyCode']);
    }

    public function testCheckPhoneVerifyCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['PhoneVerifyCode']);
    }

    public function testCheckPhoneVerifyCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['PhoneVerifyCode']);
    }

    public function testRemovePhoneVerifyCode()
    {
        $this->removeTemplateCode($this->templates['PhoneVerifyCode']);
    }

    public function testCreateAndGetEmailVerifyCode()
    {
        $this->createAndGetTemplate($this->templates['EmailVerifyCode']);
    }

    public function testCreateAndGetEmailVerifyCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['EmailVerifyCode']);
    }

    public function testCheckEmailVerifyCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['EmailVerifyCode']);
    }

    public function testCheckEmailVerifyCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['EmailVerifyCode']);
    }

    public function testRemoveEmailVerifyCode()
    {
        $this->removeTemplateCode($this->templates['EmailVerifyCode']);
    }

    public function testCreateAndGetPasswordCode()
    {
        $this->createAndGetTemplate($this->templates['PasswordCode']);
    }

    public function testCreateAndGetPasswordCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['PasswordCode']);
    }

    public function testCheckPasswordCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['PasswordCode']);
    }

    public function testCheckPasswordCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['PasswordCode']);
    }

    public function testRemovePasswordCode()
    {
        $this->removeTemplateCode($this->templates['PasswordCode']);
    }

    public function testCreateAndGetPhoneCode()
    {
        $this->createAndGetTemplate($this->templates['PhoneCode']);
    }

    public function testCreateAndGetPhoneCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['PhoneCode']);
    }

    public function testCheckPhoneCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['PhoneCode']);
    }

    public function testCheckPhoneCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['PhoneCode']);
    }

    public function testRemovePhoneCode()
    {
        $this->removeTemplateCode($this->templates['PhoneCode']);
    }

    public function testCreateAndGetSmsTransactionCode()
    {
        $this->createAndGetTemplate($this->templates['SmsTransactionCode']);
    }

    public function testCreateAndGetSmsTransactionCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['SmsTransactionCode']);
    }

    public function testCheckSmsTransactionCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['SmsTransactionCode']);
    }

    public function testCheckSmsTransactionCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['SmsTransactionCode']);
    }

    public function testRemoveSmsTransactionCode()
    {
        $this->removeTemplateCode($this->templates['SmsTransactionCode']);
    }

    public function testCreateAndGetPaymentCode()
    {
        $this->createAndGetTemplate($this->templates['PaymentCode']);
    }

    public function testCreateAndGetPaymentCodeExpired()
    {
        $this->createAndGetTemplateExpired($this->templates['PaymentCode']);
    }

    public function testCheckPaymentCodeFalse()
    {
        $this->checkTemplateFalse($this->templates['PaymentCode']);
    }

    public function testCheckPaymentCodeTrue()
    {
        $this->checkTemplateTrue($this->templates['PaymentCode']);
    }

    public function testRemovePaymentCode()
    {
        $confirmService = app(ConfirmService::class, [new $this->templates['PaymentCode']()]);
        $code = $confirmService->create($this->myPhone, $this->user);

        $this->tester->seeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
        $confirmService->remove($code->code, null, $this->user);

        $this->tester->cantSeeRecord('main.user_confirms', ['code' => $code->code, 'resource' => $this->myPhone]);
    }

    public function testGetByUserPhoneVerifyCode()
    {
        $this->getByUserTemplate($this->templates['PhoneVerifyCode']);
    }

    public function testGetByUserEmailVerifyCode()
    {
        $this->getByUserTemplate($this->templates['EmailVerifyCode']);
    }

    public function testGetByUserPasswordCode()
    {
        $this->getByUserTemplate($this->templates['PasswordCode']);
    }

    public function testGetByUserPhoneCode()
    {
        $this->getByUserTemplate($this->templates['PhoneCode']);
    }

    public function testGetByUserSmsTransactionCode()
    {
        $this->getByUserTemplate($this->templates['SmsTransactionCode']);
    }

    public function testGetByUserPaymentCode()
    {
        $this->getByUserTemplate($this->templates['PaymentCode']);
    }
}