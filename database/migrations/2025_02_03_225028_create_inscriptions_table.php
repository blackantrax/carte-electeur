<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();

            // Informations personnelles
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('telephone', 20);
            $table->string('email')->unique();
            $table->enum('genre', ['M', 'F', 'Autre']);

            // Identification
            $table->enum('type_identification', ['CNI', 'PASSEPORT', 'ATTESTATION', 'AUTRE']);
            $table->string('numero_identification');
            $table->string('numero_elecam')->unique()->nullable();

            // Localisation
            $table->string('region_code', 3)->nullable();
            $table->string('departement_code', 5);
            $table->string('commune_code', 8);
            $table->string('adresse_complete');

            // Bureau de vote
            $table->string('bureau_vote_id')->nullable();

            // Statut professionnel
            $table->enum('statut_professionnel', [
                'ETUDIANT', 'FONCTIONNAIRE', 'ENTREPRENEUR',
                'CHOMEUR', 'RETRAITE', 'AUTRE'
            ]);

            // Données électorales
            $table->boolean('est_inscrit_liste')->default(false);
            $table->integer('annee_inscription');
            $table->string('centre_vote');
            $table->string('antenne_communale')->nullable();

            // Gestion administrative
            $table->enum('status', [
                'EN_ATTENTE', 'VALIDE', 'REJETE', 'SUSPENDU'
            ])->default('EN_ATTENTE');

            $table->text('notes_rejet')->nullable();
            $table->foreignId('validateur_id')->nullable()->constrained('users');
            $table->timestamp('date_validation')->nullable();

            // Consentement & trace
            $table->boolean('consentement_rgpd')->default(false);
            $table->ipAddress('ip_inscription')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Relations étrangères
            $table->foreign('region_code')
                ->references('code')->on('regions')
                ->onDelete('cascade');

            $table->foreign('departement_code')
                ->references('code')->on('departements')
                ->onDelete('cascade');

            $table->foreign('commune_code')
                ->references('code')->on('communes')
                ->onDelete('cascade');

            $table->foreign('bureau_vote_id')
                ->references('code')->on('bureau_de_votes')
                ->onDelete('set null');

            // Index
            $table->index(['numero_elecam', 'status']);
            $table->index(['commune_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
