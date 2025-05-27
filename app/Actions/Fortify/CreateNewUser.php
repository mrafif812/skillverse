<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // parsing to boolean as from vue string is coming
        $input['isInstructor'] = filter_var($input['isInstructor'], FILTER_VALIDATE_BOOLEAN);

        // validations
        $validator = Validator::make($input, [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required']
        ]);

        $validator->sometimes(['phoneNumber', 'document', 'message'], 'required', function ($input) {
            return isset($input['isInstructor']) && $input['isInstructor'];
        });

        $validator->validate();
        
        // storing file and getting path
        $path = null;
        if(isset($input['isInstructor']) && $input['isInstructor']) {
            $path = request()->file('document')->store('users/files', 'public');
        }

        // creating user
        $user = User::create([
            'first_name' => $input['firstName'],
            'last_name' => $input['lastName'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'is_instructor' => $input['isInstructor'],
            'phone_number' => $input['phoneNumber'] ?? null,
            'document' => $path ?? null,
            'message' => $input['message'] ?? null,
        ]);

        // assigning Roles
        if(isset($input['isInstructor']) && $input['isInstructor']) {
            $user->assignRole('instructor');
        } else {
            $user->assignRole('student');
        }

        return $user;
    }
}
