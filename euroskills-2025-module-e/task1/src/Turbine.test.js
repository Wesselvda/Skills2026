const {Turbine} = require("./Turbine");

describe("Turbine", () => {
    test("Checks a valid netPower in the turbine class", () => {
        let turbine = new Turbine(1, 2, 3, 4, 5, 6, 7)

        expect(turbine.netPower(6)).toBe(4);
    });
});
