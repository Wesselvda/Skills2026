const {WakeModel} = require("./WakeModel");

describe("WakeModel", () => {
    test("Tries different speeds and expects the correct results", () => {
        let wakeModel = new WakeModel()

        expect(wakeModel.effectiveSpeed(1, 2, 3)).toBe(1)
        expect(wakeModel.effectiveSpeed(1, [1, 2, 3], 2)).toBe(0.92365)
    });
});
