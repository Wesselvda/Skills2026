const {PowerCurve} = require("./PowerCurve");

describe("PowerCurve", () => {
    test("Checks the powercurve if the powerAt function returns the correct value", () => {
        let powerCurve = new PowerCurve(1, 2, 3, 4);

        expect(powerCurve.powerAt(2)).toBe(4);
        expect(powerCurve.powerAt(0)).toBe(0);
    });
});
