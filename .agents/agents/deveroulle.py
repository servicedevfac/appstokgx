
import pikepdf
from pathlib import Path
import getpass

def deverrouiller_pdf():
    print("=== DÉVERROUILLAGE D'UN PDF ===\n")

    fichier = input("C:\\Users\\palme\\Desktop\\Livre_Exploitation_de_Texte_1_CM1_protege-1.pdf ").strip().strip('"')
    pdf_path = Path(fichier)

    if not pdf_path.is_file():
        print("Erreur : fichier introuvable.")
        return

    mot_de_passe = getpass.getpass("CFED.CM1.26 ")

    # Création du nom du fichier de sortie
    sortie = pdf_path.with_name(
        pdf_path.stem + "_deverrouille.pdf"
    )

    try:
        with pikepdf.open(pdf_path, password=mot_de_passe) as pdf:
            # Enregistrer une copie sans chiffrement
            pdf.save(sortie)

        print("\nDéverrouillage réussi !")
        print(f"Fichier créé : {sortie}")

    except pikepdf.PasswordError:
        print("Erreur : mot de passe incorrect.")

    except Exception as erreur:
        print(f"Une erreur est survenue : {erreur}")


if __name__ == "__main__":
    deverrouiller_pdf()
