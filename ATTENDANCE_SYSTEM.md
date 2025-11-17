# 📋 Système de Pointage - Documentation

## 🚀 Installation

1. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

2. **Seeder les données initiales**
   ```bash
   php artisan db:seed
   ```

3. **Configurer les tâches planifiées (Cron)**
   
   Ajoutez ces lignes à votre crontab (`crontab -e`) :
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```
   
   Puis ajoutez dans `app/Console/Kernel.php` (ou créez-le) :
   ```php
   protected function schedule(Schedule $schedule)
   {
       // Générer un nouveau QR code toutes les 30 secondes
       $schedule->command('qr:generate')->everyThirtySeconds();
       
       // Détecter les absences chaque jour à 23:59
       $schedule->command('attendance:detect-absences')->dailyAt('23:59');
   }
   ```

## 📋 Fonctionnalités

### A. Gestion Employés
- ✅ Fiche employé (nom, poste, département)
- ✅ Horaire standard : 8h/jour (configurable)
- ✅ Jours de repos configurables
- ✅ Statut actif/inactif

### B. Pointage Sécurisé
- ✅ Connexion employé (login/mot de passe)
- ✅ Scan QR Code dynamique (changé toutes les 30s)
- ✅ Géolocalisation obligatoire
- ✅ Vérification zone autorisée ±50m (configurable)
- ✅ Blocage si hors zone

### C. Calculs Automatiques
- ✅ Heures travaillées quotidiennes
- ✅ Détection absences automatiques
- ✅ Calcul heures supplémentaires
- ✅ Cumul mensuel par employé

### D. Alertes & Rapports
- ✅ Absences non justifiées
- ✅ Retards (>15min)
- ✅ Seuils heures sup dépassés
- ✅ Rapports mensuels

## 🔐 Comptes par défaut

### Admin
- **Email:** `admin@admin.com`
- **Password:** `password`

### Employés (après seed)
- **Email:** `jean.dupont@example.com` / `marie.martin@example.com`
- **Password:** `password`

## 📱 Utilisation

### Pour les Administrateurs

1. **Gérer les employés**
   - Accéder à `/employees`
   - Créer, modifier, supprimer des employés
   - Configurer les horaires et jours de repos

2. **Gérer les départements**
   - Accéder à `/departments`
   - Créer et gérer les départements

3. **Consulter les pointages**
   - Accéder à `/attendance`
   - Filtrer par employé et dates

4. **Voir le QR Code**
   - Le QR code s'affiche automatiquement sur le dashboard
   - Il se renouvelle toutes les 30 secondes

5. **Configurer la géolocalisation**
   - Accéder à `/settings`
   - Définir les coordonnées de la zone autorisée
   - Configurer le rayon (défaut: 50m)

6. **Consulter les alertes**
   - Accéder à `/alerts`
   - Voir les absences, retards, heures sup

7. **Générer des rapports**
   - Accéder à `/reports`
   - Sélectionner un employé et un mois
   - Voir le résumé mensuel

### Pour les Employés

1. **Se connecter**
   - Accéder à `/employee/login`
   - Utiliser email et mot de passe

2. **Scanner le QR Code**
   - Accéder à `/employee/qr-scanner`
   - Autoriser l'accès à la caméra
   - Autoriser la géolocalisation
   - Scanner le QR code affiché sur l'écran admin

3. **Voir son pointage du jour**
   - Sur le dashboard employé
   - Voir les heures d'entrée/sortie

## 🔧 Configuration

### Zone de géolocalisation

1. Aller dans `/settings`
2. Entrer les coordonnées GPS (latitude, longitude)
3. Définir le rayon en mètres (défaut: 50m)

### Seuil d'heures supplémentaires

1. Aller dans `/settings`
2. Définir le nombre d'heures sup par jour pour déclencher une alerte

## 📊 API Endpoints

### Pour applications mobiles

- `POST /api/employee/check-in` - Pointage d'entrée
- `POST /api/employee/check-out` - Pointage de sortie
- `GET /api/employee/today-status` - Statut du jour
- `GET /api/employee/qr-code/current` - QR code actuel

**Format de requête pour check-in/check-out:**
```json
{
    "employee_id": 1,
    "qr_code": "code_du_qr",
    "latitude": 14.7167,
    "longitude": -17.4677
}
```

## ⚙️ Commandes Artisan

- `php artisan qr:generate` - Générer un nouveau QR code
- `php artisan attendance:detect-absences [date]` - Détecter les absences (défaut: hier)

## 📝 Notes importantes

1. **QR Code dynamique**: Le QR code change toutes les 30 secondes pour la sécurité
2. **Géolocalisation**: Obligatoire pour valider un pointage
3. **Zone autorisée**: Si l'employé est hors zone, le pointage est bloqué
4. **Détection d'absences**: Automatique chaque jour à 23:59
5. **Retards**: Détectés automatiquement si > 15 minutes de retard

## 🐛 Résolution de problèmes

### QR Code ne se génère pas
- Vérifier que le cron job est configuré
- Exécuter manuellement: `php artisan qr:generate`

### Géolocalisation ne fonctionne pas
- Vérifier que le navigateur autorise la géolocalisation
- Vérifier les paramètres dans `/settings`

### Absences non détectées
- Vérifier que le cron job est configuré
- Exécuter manuellement: `php artisan attendance:detect-absences`

