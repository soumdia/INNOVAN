import os, sys

models_dir = r'C:\Hackathon\tutore_2\innovan\models'
data_dir   = r'C:\Hackathon\tutore_2\innovan\Data'

print('=== Verification des modeles ===')
files = [
    'model_coupure_v8.pkl', 'model_cause_v8.pkl',
    'le_cluster_v8.pkl', 'le_saison_v8.pkl',
    'le_cause_v8.pkl', 'le_zone_v8.pkl',
    'seuil_operateur_v8.pkl', 'feature_names_v8.pkl',
]
all_ok = True
for f in files:
    path = os.path.join(models_dir, f)
    ok = os.path.exists(path)
    if not ok:
        all_ok = False
    print(f'  {"OK" if ok else "MANQUANT"} : {f}')

print()
print('=== Verification poste_metadata.csv ===')
meta_path = os.path.join(data_dir, 'poste_metadata.csv')
if os.path.exists(meta_path):
    try:
        import pandas as pd
        df = pd.read_csv(meta_path)
        print(f'  OK : {len(df)} postes, {df["zone"].nunique()} zones, {df["quartier"].nunique()} quartiers')
    except ImportError:
        print('  OK (fichier present — pandas non installe pour lire)')
else:
    all_ok = False
    print(f'  MANQUANT : {meta_path}')

print()
print('=== Chargement modele binaire ===')
try:
    import joblib
    m = joblib.load(os.path.join(models_dir, 'model_coupure_v8.pkl'))
    print(f'  OK : RandomForest {m.n_estimators} arbres, OOB={m.oob_score_:.3f}')
except Exception as e:
    print(f'  ERREUR : {e}')

print()
if all_ok:
    print('RESULTAT : Tous les fichiers sont presents. API prete a demarrer.')
else:
    print('RESULTAT : Certains fichiers sont manquants.')
