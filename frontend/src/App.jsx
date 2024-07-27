import './App.css';
import {useEffect, useState} from "react";

function App() {
	const [ message, setMessage ] = useState({ time: "00:00:00" });
	useEffect(() => {
		let source = new EventSource("http://0.0.0.0:8000/events");
		source.onopen = function () {
			console.log("onopen");
		}
		source.onerror = function (error) {
			console.error("onerror ", error);
		}
		source.onmessage = function (event) {
			console.log(event);
			console.log("onmessage ", JSON.parse(event.data));
			setMessage(JSON.parse(event.data));
		}
		source.addEventListener('ping', function (event) {
			console.log(event);
			console.log("addEventListener ping", JSON.parse(event.data));
		});
		source.addEventListener('message', function (event) {
			console.log(event);
			console.log("addEventListener message", JSON.parse(event.data));
		});
		return () => source.close();
	}, []);
	return (
		<div>
			TIME: {message.time}
		</div>
	)
}

export default App
