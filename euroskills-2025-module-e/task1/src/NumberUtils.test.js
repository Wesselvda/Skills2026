const {clamp, sum, roundTo} = require("./NumberUtils");

describe("NumberUtils", () => {
    test("Adds 1 + 2 + 8 to equal 11", () => {
        expect(sum([1, 2, 8])).toBe(11);
    });

    test("Clamps 11 between 15 and 20 and expects 15", () => {
        expect(clamp(11, 15, 20)).toBe(15)
    })

    test("Rounds 1.25 with 1 digit and expects 1.3", () => {
        expect(roundTo(1.25, 1)).toBe(1.3);
    })
    
    test("Rounds 2.25 without specifying a digit and expects 1", () => {
        expect(roundTo(1.25)).toBe(1);
    })
});
