import asyncio
from typing import Annotated

from fastapi import FastAPI, Form, HTTPException, Request, status
from fastapi.responses import FileResponse, HTMLResponse

from .config import settings

if settings.DEBUG:
    app = FastAPI(
        debug=settings.DEBUG,
    )
else:
    app = FastAPI(
        debug=settings.DEBUG,
        docs_url=None,
        openapi_url=None,
        redoc_url=None,
    )

concurrent = asyncio.Semaphore(settings.CONCURRENT_RUNS)


async def t(request: Request, cmd: str | None = None, text: str = "", pattern: str = "") -> str:
    result = "No data yet"
    if cmd:
        try:
            proc = None
            async with concurrent, asyncio.timeout(10):
                proc = await asyncio.create_subprocess_shell(
                    cmd,
                    stdout=asyncio.subprocess.PIPE,
                    stderr=asyncio.subprocess.PIPE,
                    stdin=asyncio.subprocess.PIPE,
                )

                stdout, stderr = await proc.communicate(text.encode())

                result = f"[{cmd!r} exited with {proc.returncode}]\n"
                if stdout:
                    result += f"[stdout]\n{stdout.decode()}\n"
                if stderr:
                    result += f"[stderr]\n{stderr.decode()}\n"
        except TimeoutError as ex:
            if proc:
                proc.kill()
            raise HTTPException(status.HTTP_418_IM_A_TEAPOT) from ex

    return f"""
    <h1> Grep ass a Service! </h1>
    <br>
    <script>alert("hello from Marcus")</script>
    <form method="post" action="{request.url_for("work")}">
        <label for="text">Text:</label> <textarea type="text" name="text">{text}</textarea> <br>
        <label for="pattern">Pattern:</label> <input type="text" name="pattern" value="{pattern}"> <br>
        <input type="submit" value="Submit">
    </form>
    <br>
    <pre>{result.strip()}</pre>
""".strip()


@app.get("/")
async def index(request: Request) -> HTMLResponse:
    return HTMLResponse(await t(request))


@app.post("/work")
async def work(
    request: Request,
    text: Annotated[str, Form()],
    pattern: Annotated[str, Form()],
) -> HTMLResponse:
    if len(text) > settings.LIMIT * 2 or len(pattern) > settings.LIMIT:
        raise HTTPException(status_code=status.HTTP_413_REQUEST_ENTITY_TOO_LARGE)

    print(f"{pattern = }")

    return HTMLResponse(
        await t(
            request,
            cmd=f"""grep {pattern}""",
            text=text,
            pattern=pattern,
        ),
    )
