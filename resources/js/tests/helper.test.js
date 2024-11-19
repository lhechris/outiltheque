import {beforeEach,afterAll,beforeAll,expect,test,describe,vi} from 'vitest'
import {validateEmail,validateTel,request,upload_file } from '../helper'
import axios from 'axios'

/** =============================================
 * Test EMAILs
 ================================================*/

/* La liste des emails a tester */
var goodmail = ['coucou@example.com','avec-tiret@example.com','avec.point@example.com']
var badmail = ['sansextension@example','sansarobase-example.com','@example.com',null,'']

describe('validateEmail', () => {
    describe('Good', () => {
        for (let i=0;i<goodmail.length;i++) {
            test ('email '+goodmail[i], () => {
                expect(validateEmail(goodmail[i])).toBe(true)
            })
        }
    })

    describe('Bad', () => {
        for (let i=0;i<badmail.length;i++) {
            test ('email '+badmail[i], () => {
                expect(validateEmail(badmail[i])).toBe(false)
            })
        }
    })
})

/** =============================================
 * Test Telephone
 ================================================*/

/* La liste des tel a tester */
var goodtel = ['0102030405','01 02 03 04 05']
var badtel = ['',' ', 'fff01', '01020304', '01020304050']

describe('validate Telephone', () => {
    describe('Good', () => {
        for (let i=0;i<goodtel.length;i++) {
            test ('tel '+goodtel[i], () => {
                expect(validateTel(goodtel[i])).toBe(true)
            })
        }
    })

    describe('Bad', () => {
        for (let i=0;i<badtel.length;i++) {
            test ('tel '+badtel[i], () => {
                expect(validateTel(badtel[i])).toBe(false)
            })
        }
    })
})


/** =============================================
 * Test request function
 ================================================*/
 vi.mock('axios')
 
 describe('request', () => {

    beforeEach(() => {
        axios.get.mockReset()
        axios.post.mockReset()
        axios.put.mockReset()
        axios.delete.mockReset()
        localStorage.setItem('APP_DEMO_USER_TOKEN',"0123456789")
    })

    beforeAll( () => {
        
    })

    afterAll( () => {
        localStorage.removeItem('APP_DEMO_USER_TOKEN')
    })

    const headers = {
        headers: {
            Authorization: 'Bearer 0123456789' ,
        }
      }


    test('makes a request without token in storage', async () => {
        const dataMock = [{ id: 1 }, { id: 2 }]
        axios.get.mockResolvedValue(
            dataMock
        )
        localStorage.removeItem('APP_DEMO_USER_TOKEN')
        const ret = await request("get","http://example.com")
        expect(ret).toStrictEqual(dataMock)

    })

    test('makes a request to get something', async () => {
        const dataMock = [{ id: 1 }, { id: 2 }]
        axios.get.mockResolvedValue(
            dataMock
        )

        const ret = await request("get","http://example.com")
        expect(axios.get).toHaveBeenCalledWith('http://example.com',headers)
        expect(ret).toStrictEqual(dataMock)

    })

    test('makes a request to post something', async () => {
        const inputMock = [{ id: 1 }, { id: 2 }]
        const outputMock = [{ id: 3 }, { id: 4 }]
        axios.post.mockResolvedValue(
            outputMock
        )

        const headers = {
            headers: {
                Authorization: 'Bearer 0123456789' ,
            }
        }

        const ret = await request("post","http://example.com",inputMock)
        expect(axios.post).toHaveBeenCalledWith('http://example.com',inputMock,headers)
        expect(ret).toStrictEqual(outputMock)

    })

    test('makes a request to put something', async () => {
        const inputMock = [{ id: 1 }, { id: 2 }]
        const outputMock = [{ id: 3 }, { id: 4 }]
        axios.put.mockResolvedValue(
            outputMock
        )

        const headers = {
            headers: {
                Authorization: 'Bearer 0123456789' ,
            }
        }

        const ret = await request("put","http://example.com",inputMock)
        expect(axios.put).toHaveBeenCalledWith('http://example.com',inputMock,headers)
        expect(ret).toStrictEqual(outputMock)

    })

    test('makes a request to delete something', async () => {
        const outputMock = [{ id: 3 }, { id: 4 }]
        axios.delete.mockResolvedValue(
            outputMock
        )

        const headers = {
            headers: {
                Authorization: 'Bearer 0123456789' ,
            }
        }

        const ret = await request("delete","http://example.com")
        expect(axios.delete).toHaveBeenCalledWith('http://example.com',headers)
        expect(ret).toStrictEqual(outputMock)

    })

    test('makes a request with bad method', async () => {

        const ret = await request("truc","http://example.com")
        expect(ret).toStrictEqual(null)

    })

})
 


/** =============================================
 * Test uploadfile function
 ================================================*/
 describe('upload_file', () => {

    beforeEach(() => {
        axios.post.mockReset()
        localStorage.setItem('APP_DEMO_USER_TOKEN',"0123456789")
    })

    afterAll( () => {
        localStorage.removeItem('APP_DEMO_USER_TOKEN')
    })

    const headers = {
        headers: {
            Authorization: 'Bearer 0123456789' ,
            "Content-Type" : 'multipart/form-data'
        }
      }


    test('upload file without token in storage', async () => {

        localStorage.removeItem('APP_DEMO_USER_TOKEN')
        const ret = await upload_file("get","http://example.com")
        expect(ret).toStrictEqual(false)

    })

    test('upload_file', async () => {
        const dataMock = [{ id: 1 }, { id: 2 }]
        axios.post.mockResolvedValue(
            dataMock
        )
        let formData = new FormData();
        formData.append("file", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");

        const ret = await upload_file("http://example.com","ABCDEFGHIJKLMNOPQRSTUVWXYZ")
        expect(axios.post).toHaveBeenCalledWith('http://example.com',formData,headers)
        expect(ret).toStrictEqual(dataMock)

    })
})