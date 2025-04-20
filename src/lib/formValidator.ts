const formValidator = ({ name = "", age = 0 }) => {
	// Errors is empty object upon initializing
	let errors: { [key: string]: string } = {};

	if (name.length == 0) {
		errors.name = "Name is required";
	}

	if (age == 0) {
		errors.age = "Invalid Age";
	}

	return errors;
};

export default formValidator;