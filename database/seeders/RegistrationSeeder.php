<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Registration;
class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $registrations = [
            [
                'psa_id' => 4798,
                'prc_number' => 144513,
                'last_name' => 'ALBANO-URSOS',
                'first_name' => 'ALYSSA MAE',
                'middle_name' => 'D.',
                'hospital_name' => 'Davao Regional Medical Center',
                'hospital_address' => 'Tagum, Davao',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9176797992',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/rYYJ1nSJt1BMBjueQKYVDB3GGVSUsMuLa7zdH5ee.png',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 5540,
                'prc_number' => 147248,
                'last_name' => 'SAN DIEGO',
                'first_name' => 'ANICA PAULINA CAMILLE',
                'middle_name' => 'C.',
                'hospital_name' => 'Cardinal Santos Medical Center ',
                'hospital_address' => 'San Juan, Metro Manila',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9178826422',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/rJ7qNbEi5AHLLv1aXCu6nLaxzElR1HJ9L8ZFMPFT.png',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 1306,
                'prc_number' => 96079,
                'last_name' => 'MARIANO',
                'first_name' => 'KATHERINE HAPPINESS',
                'middle_name' => 'G.',
                'hospital_name' => 'Metro San Jose Medical Center ',
                'hospital_address' => 'Banay-Banay 2nd, San Jose, Batangas',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9175243947',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/OCEwmOKVaonVfSiZrjbb0QxlF4fsF9arOF9H28LP.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 1243,
                'prc_number' => 73720,
                'last_name' => 'MALABANAN',
                'first_name' => 'MARIA LUISA',
                'middle_name' => 'C.',
                'hospital_name' => 'Ospital ng Maynila Medical Center',
                'hospital_address' => 'Quirino Avenue, Malate, Manila',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9178640531',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/FDoKSoddvMeQT7Y3tKYMB0F7gVSZdaHCVieADOEQ.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 1344,
                'prc_number' => 60667,
                'last_name' => 'MENDOZA',
                'first_name' => 'LEOPOLDO',
                'middle_name' => 'B.',
                'hospital_name' => 'Heart of Jesus Hospital',
                'hospital_address' => 'Sobrepeña Drive, Bgy Sto Niño 1st, San Jose City, Nueva Ecija',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9228007530',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/cr7itG3rPyWVfvoe5PmFJBI5sGijiGhbPjmsJOTM.png',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 2763,
                'prc_number' => 104893,
                'last_name' => 'AMORIN, II',
                'first_name' => 'NONILO',
                'middle_name' => 'B.',
                'hospital_name' => 'VSMMC ',
                'hospital_address' => 'Cebu',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9323555333',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/AvaYC0lTARtYDbeyJ2mXExBUveyj0HC0FQZIQZaz.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 3964,
                'prc_number' => 128648,
                'last_name' => 'TAWAGEN',
                'first_name' => 'MARYBETH',
                'middle_name' => 'PANONOT',
                'hospital_name' => 'Luis Hora Memorial Regional Hospital',
                'hospital_address' => 'Bauko, Mountain Province',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9322945380',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/0HTTrIX0taiV0JT7YZR2qqXq2o6NA3UkASArBu7z.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 3365,
                'prc_number' => 117161,
                'last_name' => 'CENIZA',
                'first_name' => 'NICHOLSON',
                'middle_name' => 'E.',
                'hospital_name' => 'PSH',
                'hospital_address' => '2742 el gusto drive mabolo',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9778126533',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/AwUSruyipsv40qAUMU9fsA662yMNEk1ZZ5B7lMxV.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 4154,
                'prc_number' => 131520,
                'last_name' => 'ROSALES',
                'first_name' => 'AILEEN',
                'middle_name' => 'L.',
                'hospital_name' => 'Makati Medical Center ',
                'hospital_address' => 'No 2 amorsolo st Legaspi village makati city',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9153770701',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/dyOxOR8JrSgBIuKlcigV7fs9qWxCbr9K0vWRJ4gM.png',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],

            [
                'psa_id' => 4331,
                'prc_number' => 135607,
                'last_name' => 'CHAVEZ',
                'first_name' => 'EMMANUEL',
                'middle_name' => 'P.',
                'hospital_name' => 'Mary Mediatrix Medical Center',
                'hospital_address' => 'Lipa City, Batangas',
                'email' => 'rezerdire30@gmail.com',
                'contact_number' => '9174698843',
                'membership' => 'RM',
                'discount_id' => null,
                'proof_payment' => 'Registration/ProofofPayment/19g0TS3wdEuJuTCHSnEJpBeqyheql37R5EUxd64d.jpg',
                'status' => Registration::STATUS_PENDING,
                'country' => 'Philippines',
                'rejection_title' => null,
                'rejection_reason' => null,
            ],
        ];

        foreach ($registrations as $registration) {
            Registration::create($registration);
        }

        $this->command->info('10 registration records seeded successfully.');
    
    }
}
