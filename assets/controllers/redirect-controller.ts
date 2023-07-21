import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static override targets = ['countdown'];
    static override values = {countdown: Number, url: String};

    private interval: number;

    private countdownTarget: HTMLSpanElement;

    private countdownValue: number;
    private urlValue: string;

    public connect() {
        this.interval = window.setInterval(() => {
            this.countdownValue -= 1;
            this.countdownTarget.innerText = String(this.countdownValue);
            if (this.countdownValue === 0) {
                clearInterval(this.interval);
                window.location.href = this.urlValue;
            }
        }, 1_000);
    }
}
