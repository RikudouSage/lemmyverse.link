import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    private readonly domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9]$/;

    static override targets = [
        'preferredInstances',
        'customInstanceInputWrapper',
        'customInstanceInput',
        'errorText',
    ];
    static override values = {
        cookieName: String,
        redirectUrl: String,
        emptyInputError: String,
        invalidValueError: String,
    };

    private preferredInstancesTarget: HTMLDivElement;
    private customInstanceInputWrapperTarget: HTMLDivElement;
    private customInstanceInputTarget: HTMLInputElement;
    private errorTextTarget: HTMLParagraphElement;

    private cookieNameValue: string;
    private redirectUrlValue: string;
    private emptyInputErrorValue: string;
    private invalidValueErrorValue: string;

    public toggleInstanceRow(): void {
        const className = 'hidden';
        this.preferredInstancesTarget.classList.contains(className)
            ? this.preferredInstancesTarget.classList.remove(className)
            : this.preferredInstancesTarget.classList.add(className)
        ;
    }

    public saveInstance(event: Event): void {
        const target = <HTMLButtonElement>event.target;
        const instance = target.dataset.instance;

        this.savePreference(instance);
        window.location.href = this.redirectUrlValue;
    }

    public showCustomInstanceField(): void {
        this.customInstanceInputWrapperTarget.classList.remove('hidden');
    }

    public saveCustomInstance(): void {
        this.errorTextTarget.innerText = '';
        if (!this.customInstanceInputTarget.value) {
            this.errorTextTarget.innerText = this.emptyInputErrorValue;
            return;
        }
        if (!this.domainRegex.test(this.customInstanceInputTarget.value)) {
            this.errorTextTarget.innerText = this.invalidValueErrorValue;
            return;
        }

        this.savePreference(this.customInstanceInputTarget.value);
        window.location.href = this.redirectUrlValue;
    }

    private savePreference(instance: string): void {
        const targetDate = new Date();
        targetDate.setFullYear(targetDate.getFullYear() + 100);

        document.cookie = `${this.cookieNameValue}=${instance}; expires=${targetDate.toString()}; path=/`
    }
}
