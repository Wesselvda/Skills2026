const { Turbine } = require("./Turbine");
const {WindFarm} = require("./WindFarm");

describe("WindFarm", () => {
    test("Tests an empty windfarm to throw an error", () => {
        expect(() => {
            new WindFarm()
        }).toThrow(
            new Error("WindFarm requires at least one turbine")
        );
    })

    test("Test a valid windfarm config to return the correct totalpower", () => {
        let windFarm = new WindFarm([
            new Turbine(1, {x: 1, y: 2}, 3, 4, 5, 6, 7),
            new Turbine(2, {x: 3, y: 2}, 3, 4, 5, 6, 7),
        ]);

        expect(windFarm.totalPower(6)).toBe(8)
    });
});
