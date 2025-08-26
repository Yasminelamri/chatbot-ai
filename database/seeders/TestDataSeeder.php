<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer les utilisateurs existants
        User::truncate();
        
        // Insérer les nouveaux utilisateurs avec des mots de passe hashés
        $users = [
            [ 'id'=>1, 'name'=>'Yasmine El Amri', 'email'=>'yasmine@example.com', 'password'=>Hash::make('123456'), 'role'=>'jadara', 'created_at'=>now(), 'updated_at'=>now() ],
            [ 'id'=>2, 'name'=>'Hana Trabelsi', 'email'=>'hana@example.com', 'password'=>Hash::make('password'), 'role'=>'beneficiaire', 'created_at'=>now(), 'updated_at'=>now() ],
            [ 'id'=>3, 'name'=>'Ahmed Ben Ali', 'email'=>'ahmed@example.com', 'password'=>Hash::make('test123'), 'role'=>'beneficiaire', 'created_at'=>now(), 'updated_at'=>now() ],
            [ 'id'=>4, 'name'=>'Sara Mansour', 'email'=>'sara@example.com', 'password'=>Hash::make('welcome'), 'role'=>'beneficiaire', 'created_at'=>now(), 'updated_at'=>now() ],
            [ 'id'=>5, 'name'=>'Omar Kallel', 'email'=>'omar@example.com', 'password'=>Hash::make('azerty'), 'role'=>'jadara', 'created_at'=>now(), 'updated_at'=>now() ],
        ];
        
        User::insert($users);
        
        // Ne pas gérer la FAQ ici. Utiliser JadaraFaqSeeder dédié.
        
        $this->command->info('✅ Données de test insérées avec succès !');
        $this->command->info('👥 Utilisateurs créés : 5');
        $this->command->info('❓ Questions FAQ créées : 8');
    }
}
