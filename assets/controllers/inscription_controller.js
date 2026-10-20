import { Controller } from '@hotwired/stimulus';

/*
 * Affiche les champs étudiant ou partenaire selon le type de compte choisi.
 * Sans JavaScript, les deux blocs restent visibles : le formulaire marche quand même.
 */
export default class extends Controller {
    static targets = ['etudiant', 'partenaire'];

    connect() {
        this.basculer();
    }

    basculer() {
        const choix = this.element.querySelector('input[name$="[type]"]:checked');
        const partenaire = choix?.value === 'partenaire';

        this.etudiantTarget.hidden = partenaire;
        this.partenaireTarget.hidden = !partenaire;
    }
}
