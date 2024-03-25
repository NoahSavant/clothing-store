<?php

namespace App\Services;

use App\Constants\AuthenConstants\StatusResponse;
use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Constants\UserConstants\UserVerifyTime;
use App\Http\Resources\UserInformation;
use DateInterval;
use DateTime;

class AuthenService extends BaseService
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
        $this->model = null;
    }

    public function authenCreadentials($credentials)
    {
        return auth()->attempt($credentials);
    }

    public function setUpUser($user)
    {
        $verify_code = $this->createVerify($user);
        $this->sendMail("Verification Code Mail", 'emails.send-verify-code', [
                'name' => $user->username,
                'verifyCode' => $verify_code,
        ], $user->email);
    }

    public function login($input)
    {
        $remember = $input['remember'] ?? false;
        $rememberToken = null;
        $credentials = ['email' => $input['email'], 'password' => $input['password']];

        if (! $token = $this->authenCreadentials($credentials)) {
            return $this->response(['message' => 'User authentication failed'], StatusResponse::UNAUTHORIZED);
        }

        if ($remember) {
            $rememberToken = $this->encryptToken(array_merge(
                $credentials,
                [
                    'remember' => true,
                    'time' => now(),
                ]
            ));
        }

        return $this->createNewToken($token, $rememberToken);
    }

    public function signUp($input)
    {
        if ($this->userService->isEmailExist($input['email'])) {
            return $this->response([
                'message' => 'This email has been used',
            ], StatusResponse::ERROR);
        }

        $user = $this->userService->create(array_merge($input,
            [
                'password' => $this->hash($input['password']),
                'role' => UserRole::CUSTOMER,
                'status' => UserStatus::DEACTIVE,
            ]
        ));

        $this->setUpUser($user);

        return $this->response([
            'message' => $user ? 'User successfully registered' : 'User fail registered',
        ], $user ? StatusResponse::SUCCESS : StatusResponse::ERROR);
    }

    public function activeAccount($input)
    {
        $email = $input['email'];
        $verifyCode = $input['verify_code'];

        $user = $this->userService->model->where('email', $email)->where('verify_code', $verifyCode)->first();

        if (!$user) {
            return $this->response([
                'message' => 'Could not find a user with a valid authentication token',
            ], StatusResponse::ERROR);
        }

        if ($user->overtimed_at < now()) {
            $user->verify_code = null;
            $user->overtimed_at = null;
            $user->save();
            return $this->response([
                'message' => 'This verify code is expired',
            ], StatusResponse::ERROR);
        }

        $user->verify_code = null;
        $user->overtimed_at = null;
        $user->status = UserStatus::ACTIVE;
        $user->save();

        return $this->response([
            'message' => 'Account activated successfully',
        ], StatusResponse::SUCCESS);
    }


    public function getEmailInforFromToken($token)
    {
        [$header, $payload, $signature] = explode('.', $token);

        $decodedPayload = json_decode(base64_decode($payload), true);

        return $decodedPayload;
    }

    public function signupWithGoogle($input)
    {
        $gmailToken = $input['gmail_token'];
        $data = $this->getEmailInforFromToken($gmailToken['id_token']);

        if ($this->userService->isEmailExist($data['email'])) {
            return $this->response([
                'message' => 'This email has been used',
            ], StatusResponse::ERROR);
        }

        $user = $this->userService->create([
            'email' => $data['email'],
            'name' => $input['name'],
            'password' => $this->hash($input['password']),
            'role' => UserRole::CUSTOMER,
            'status' => UserStatus::ACTIVE,
            'image_url' => $data['picture'],
        ]);

        //$this->setUpUser($user);

        return $this->response([
            'message' => $user ? 'User successfully registered' : 'User fail registered',
            'user' => $user,
        ], $user ? StatusResponse::SUCCESS : StatusResponse::ERROR);
    }

    public function generateEncodedString($startTime, $endTime, $userData)
    {
        $dataToEncode = $startTime.'|'.$endTime.'|'.$userData;

        return $this->hash($dataToEncode);
    }

    public function createVerify($user)
    {   
        $currentDateTime = new DateTime();
        $overDateTime = clone $currentDateTime;
        $overDateTime->add(new DateInterval(UserVerifyTime::ACTIVE_TIME));
        
        $verifyCode = $this->generateEncodedString($currentDateTime->format('Y-m-d H:i:s'), $overDateTime->format('Y-m-d H:i:s'), json_encode($user));
        $overtimed_at = $overDateTime->format('Y-m-d H:i:s');

        $user->verify_code = $verifyCode;
        $user->overtimed_at = $overtimed_at;
        $user->save();

        return $verifyCode;
    }

    public function sendVerify($input)
    {
        $user = $this->userService->getBy('email', $input['email']);

        if (!$user) {
            return $this->response([
                'message' => 'Can not find out the email',
            ], StatusResponse::ERROR);
        }

        $verify_code = $this->createVerify($user);

        $this->sendMail("Verification Code Mail", 'emails.send-verify-code', [
            'name' => $user->username,
            'verifyCode' => $verify_code,
        ], $user->email);

        return $this->response([
            'message' => 'Send verify code successfully',
        ], StatusResponse::SUCCESS);
    }

    public function resetPassword($input) {
        $email = $input['email'];
        $verifyCode = $input['verify_code'];
        $newPassword = $input['new_password'];

        $user = $this->userService->model->where('email', $email)->where('verify_code', $verifyCode)->first();

        if (!$user or $user->status !== UserStatus::ACTIVE) {
            return $this->response([
                'message' => 'Could not find a user with a valid authentication token',
            ], StatusResponse::ERROR);
        }

        if ($user->overtimed_at < now()) {
            $user->verify_code = null;
            $user->overtimed_at = null;
            $user->save();
            return $this->response([
                'message' => 'This verify code is expired',
            ], StatusResponse::ERROR);
        }

        $user->verify_code = null;
        $user->overtimed_at = null;
        $user->password = $this->hash($newPassword);
        $user->save();

        return $this->response([
            'message' => 'Reset password successfully',
        ], StatusResponse::SUCCESS);
    }

    public function throwAuthenError()
    {
        return $this->response(['message' => 'You need to login to access'], StatusResponse::UNAUTHORIZED);
    }

    public function throwAuthorError()
    {
        return $this->response(['message' => 'You do not have permission to access'], StatusResponse::UNAUTHORIZED);
    }

    public function refresh($rememberToken)
    {
        $user = $this->userService->getBy('remember_token', $rememberToken);

        if (! $user) {
            return false;
        }

        $data = $this->decryptToken($rememberToken);

        return $this->login($data);
    }

    public function getUserProfile()
    {
        return $this->response(auth()->user(), StatusResponse::SUCCESS);
    }

    public function createNewToken($token, $rememberToken)
    {
        $user = auth()->user();

        if ($user->status == UserStatus::DEACTIVE) {
            return $this->response([
                'message' => 'Your account is deactived',
            ], StatusResponse::DEACTIVED_ACCOUNT);
        }

        if ($user->status == UserStatus::BLOCK) {
            return $this->response([
                'message' => 'Your account is blocked',
            ], StatusResponse::BLOCKED_ACCOUNT);
        }

        $user->remember_token = $rememberToken;
        $user->save();

        return $this->response([
            'access_token' => $token,
            'remember_token' => $rememberToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => json_decode(json_encode(new UserInformation(auth()->user()))),
        ], StatusResponse::SUCCESS);
    }
}
